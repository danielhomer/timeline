<?php 

class GitHub extends TimelineService {

	public $username;
	public $url = 'https://api.github.com/';
	public $home_url = 'https://github.com/';

	public function __construct()
	{
		$this->username = get_option( 'timeline_option_github' )['username'];
	}

	public function sync()
	{
		$events = $this->get( $this->url, $this->username );

		if ( ! $events || empty( $events ) )
			return;

		$i = 0;
		foreach ( $events as $event ) {
			if ( $i === 0 && TimelinePost::get( $event->id ) )
				return false;

			$timelinePost = new TimelinePost();
			$timelinePost->service = "github";
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
					return 'Created a branch of <a href="' . $this->home_url . $event->repo->name . '">' . $event->repo->name . '</a> called ' . $event->payload->ref;
				break;

			case 'DeleteEvent':
				if ( $event->payload->ref_type == 'branch' )
					return 'Deleted branch ' . $event->payload->ref;

				if ( $event->payload->ref_type == 'tag' )
					return 'Deleted tag ' . $event->payload->ref;
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

			case 'IssuesEvent':
				if ( $event->payload->action == 'opened' )
					return 'Opened a new <a href="' . $event->payload->issue->url . '">Issue</a>';

				if ( $event->payload->action == 'cloased' )
					return 'Closed an <a href="' . $event->payload->issue->url . '">Issue</a>';

				if ( $event->payload->action == 'reopened' )
					return 'Reopened an <a href="' . $event->payload->issue->url . '">Issue</a>';

				break;

			case 'PublicEvent':
				//This is triggered when a private repo is open sourced. Without a doubt: the best GitHub event.
				break;

			case 'PushEvent':
				return 'Pushed ' . $event->payload->head . ' to <a href="' . $this->home_url . $event->repo->name . '">' . $event->repo->name . '</a> on branch ' . $event->payload->ref;
				break;

			case 'WatchEvent':
				return 'Watched <a href="' . $this->home_url . $event->repo->name . '">' . $event->repo->name . '</a>';
				break;

			default:
				return false;
				break;
		}
	}

}

?>