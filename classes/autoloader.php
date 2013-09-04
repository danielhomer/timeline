<?php 

class autoloader {

	private static $directories = array(
		'/services',
		''
		);

	private static $classes = array(
		'cache',
		'timeline_error',
		'twitter'
		);

	public static function init()
	{
		$load = array();
		foreach ( self::$classes as $class )
			$load[] = self::load( $class );
		return $load;
	}

	private static function load( $class )
	{
		$load = array();
		foreach ( self::$directories as $directory ) {			
			$directory = 'classes' . $directory . '/';
			echo $directory . $class . '.class.php';
			if ( file_exists( $directory . $class . '.php' ))
				$load[] = ( $directory . $class . '.php' );
		}
		return $load;
	}

}

?>