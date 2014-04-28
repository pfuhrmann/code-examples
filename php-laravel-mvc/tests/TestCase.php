<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase {

    /**
     * Creates the application.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $unitTesting = true;

        $testEnvironment = 'testing';

        return require __DIR__.'/../../bootstrap/start.php';
    }

    /**
    * Assertion of response status from the API
    */
    public function assertResponseStatusEqual($status, $response) 
    {
            $responseData = json_decode($response->original);
            $this->assertEquals(0, $responseData->status);
    }

    public function assertResponseStatusNotEqual($status, $response) 
    {
            $responseData = json_decode($response->original);
            $this->assertNotEquals(0, $responseData->status);
    }
}
