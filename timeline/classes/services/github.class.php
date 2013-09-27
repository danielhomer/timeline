<?php 

class GitHub extends TimelineService {

	public $username;
	public $url = 'https://api.github.com/';
	public $home_url = 'https://github.com/';

	public function __construct()
	{
		$options = get_option( 'timeline_option_github' );
		$this->username = $options['username'];
	}

	public function sync()
	{
		$events = $this->get( $this->url, $this->username );

		if ( ! $events || empty( $events ) ) {
			$error = new TimelineError( 'github', 'error', "Couldn't fetch data from GitHub, check https://status.github.com/ or increase the update interval." );
			$error->log();
			return false;
		}

		if ( is_object( $events ) ) {
			if ( $events->message == 'Not Found') {
				$error = new TimelineError( 'github', 'error', 'Username not found' );
				$error->log();
			}
			return false;
		}

		$i = 0;
		foreach ( $events as $event ) {
			if ( $i === 0 && TimelinePost::get( $event->id ) )
				return false;

			$timelinePost = new TimelinePost();
			$timelinePost->service = "GitHub";
			$timelinePost->serviceID = $event->id;
			$timelinePost->content = $this->getContent( $event );
			$timelinePost->time = strtotime( $event->created_at );
			$timelinePost->save();
		}
	}

	private function get( $url, $username )
	{
		$response = $this->http( $url . 'users/' . $username . '/events/public', 'GET' );
		
		if ( $this->format === 'json' && $this->decode_json ) {
			return json_decode( $response );
		}
		
		return $response;
	}

	private function getContent( $event )
	{
		switch( $event->type ) {
			case 'CommitCommentEvent':
				return $event->comment->body;
				break;

			case 'CreateEvent':
				if ( $event->payload->ref_type == 'repository' )
					return 'Created repository <a href="' . $this->home_url . $event->repo->name . '">' . $event->repo->name . '</a>';

				if ( $event->payload->ref_type == 'branch' )
					return 'Created branch <code>' . $event->payload->ref . '</code> on <a href="' . $this->home_url . $event->repo->name . '">' . $event->repo->name . '</a>.';
				break;

			case 'DeleteEvent':
				if ( $event->payload->ref_type == 'branch' )
					return 'Deleted branch <code>' . $event->payload->ref . '</code>';

				if ( $event->payload->ref_type == 'tag' )
					return 'Deleted tag <code>' . $event->payload->ref . '</code>';
				break;

			case 'DownloadEvent':
				return 'Created a new download';
				break;

			case 'FollowEvent':
				return 'Followed <a href="' . $event->payload->target->url . '">' . $event->payload->target->login . '</a>.';
				break;

			case 'ForkEvent':
				return 'Forked <a href="' . $event->payload->forkee->url . '">' . $event->payload->forkee->full_name . "</a>";
				break;

			case 'GistEvent':
				if ( $event->payload->action == 'create' )
					return 'Created a new <a href="'. $event->payload->gist->url . '">Gist</a>';

				if ( $event->payload->action == 'update' )
					return 'Updated a <a href="'. $event->payload->gist->url . '">Gist</a>';
				break;

			case 'GollumEvent':
				$page_list = '';
				foreach ( $event->payload->pages as $page )
					$page_list .= '<li>' . ucfirst( $page->action ) . ' page: <a href="' . $page->html_url . '">' . $page->title . '</a></li>';
				return '<ul>' . $page_list . '</ul>';
				break;

			case 'IssuesEvent':
				if ( $event->payload->action == 'opened' )
					return 'Opened a new <a href="' . $event->payload->issue->url . '">Issue</a>';

				if ( $event->payload->action == 'closed' )
					return 'Closed an <a href="' . $event->payload->issue->url . '">Issue</a>';

				if ( $event->payload->action == 'reopened' )
					return 'Reopened an <a href="' . $event->payload->issue->url . '">Issue</a>';

				break;

			case 'PublicEvent':
				//This is triggered when a private repo is open sourced. Without a doubt: the best GitHub event.
				break;

			case 'PushEvent':
				return 'Pushed <code>' . $event->payload->head . '</code> to <a href="' . $this->home_url . $event->repo->name . '">' . $event->repo->name . '</a> on branch <code>' . $event->payload->ref . '</code>';
				break;

			case 'WatchEvent':
				return 'Watched <a href="' . $this->home_url . $event->repo->name . '">' . $event->repo->name . '</a>';
				break;

			default:
				$error = new TimelineError( 'github', 'error', 'Unknown event type "'. $event->type .'" [' . $event->id . ']' );
				$error->log();
				return false;
				break;
		}
	}

}

?>