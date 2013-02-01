<?php namespace Orchestra;

use \Bundle,
	\Event,
	\IoC,
	\Session,
	Laravel\View as V;

class View extends V {

	/**
	 * Theme group name
	 *
	 * @var string
	 */
	public static $theme = 'frontend';

	/**
	 * Determine if the given view exists.
	 *
	 * @param  string       $view
	 * @param  boolean      $return_path
	 * @return string|bool
	 */
	public static function exists($view, $return_path = false)
	{
		if (starts_with($view, 'name: ')
			and array_key_exists($name = substr($view, 6), static::$names))
		{
			$view = static::$names[$name];
		}

		// Run `orchestra.started: view` event and clear it.
		Event::fire('orchestra.started: view');
		Event::clear('orchestra.started: view');

		$view = Theme::resolve()->parse($view);

		if (starts_with($view, 'path: '))
		{
			$path = substr($view, 6);
		}
		else
		{
			list($bundle, $view) = Bundle::parse($view);

			$view = str_replace('.', '/', $view);

			// We delegate the determination of view paths to the view loader
			// event so that the developer is free to override and manage the
			// loading of views in any way they see fit for their application.
			$path = Event::until(static::loader, array($bundle, $view));
		}

		if ( ! is_null($path))
		{
			return $return_path ? $path : true;
		}

		return false;
	}

	/**
	 * Create a new view instance.
	 *
	 * <code>
	 *		// Create a new view instance
	 *		$view = new View('home.index');
	 *
	 *		// Create a new view instance of a bundle's view
	 *		$view = new View('admin::home.index');
	 *
	 *		// Create a new view instance with bound data
	 *		$view = new View('home.index', array('name' => 'Taylor'));
	 * </code>
	 *
	 * @access public
	 * @param  string  $view
	 * @param  array   $data
	 * @return void
	 */
	public function __construct($view, $data = array())
	{
		// Run `orchestra.started: view` event and clear it.
		Event::fire('orchestra.started: view');
		Event::clear('orchestra.started: view');

		$view       = Theme::resolve()->parse($view);
		$this->view = $view;
		$this->data = $data;

		// In order to allow developers to load views outside of the normal
		// loading conventions, we'll allow for a raw path to be given in
		// place of the typical view name, giving total freedom on view
		// loading.
		if (starts_with($view, 'path: '))
		{
			$this->path = substr($view, 6);
		}
		else
		{
			$this->path = $this->path($view);
		}

		// If a session driver has been specified, we will bind an instance
		// of the validation error message container to every view. If an
		// error instance exists in the session, we will use that instance.
		if ( ! isset($this->data['errors']))
		{
			if (Session::started() and Session::has('errors'))
			{
				$this->data['errors'] = Session::get('errors');
			}
			else
			{
				$this->data['errors'] = new Messages;
			}
		}
	}
}
