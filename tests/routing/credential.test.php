<?php

Bundle::start('orchestra');

class RoutingCredentialTest extends Orchestra\Testable\TestCase {

	/**
	 * Test Request GET (orchestra)/credential/login
	 * 
	 * @test
	 */
	public function testGetLoginPage()
	{
		$response = $this->call('orchestra::credential@login', array());

		$this->assertInstanceOf('Laravel\Response', $response);
		$this->assertEquals(200, $response->foundation->getStatusCode());
		$this->assertEquals('orchestra::credential.login', $response->content->view);
	}

	/**
	 * Test Request POST (orchestra)/credential/login
	 * 
	 * @test
	 */
	public function testPostLogin()
	{
		$response = $this->call('orchestra::credential@login', array(), 'POST', array(
			'username' => 'example@test.com',
			'password' => '123456',
			Session::csrf_token => Session::token(),
		));

		$this->assertInstanceOf('Laravel\Redirect', $response);
		$this->assertEquals('http://localhost/admin/', $response->foundation->headers->get('location'));
	}
}