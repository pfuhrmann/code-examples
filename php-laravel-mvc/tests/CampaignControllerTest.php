<?php

class CampaignControllerTest extends TestCase {

    public function testAll() 
    {
        /*$Advertiser = new Advertiser;
        $Advertiser->user_id = 1;
        $campaign1 = Factory::make('Campaign');
        $campaign2 = Factory::make('Campaign');
        $Advertiser['camapaign'] = [$campaign1, $campaign2];
        $advertiserWithCampAndCrea = [
                $Advertiser
        ];
        
        $mock = Mockery::mock('AdvertiserRepositoryInterface');
        $mock->shouldReceive('allWithCampaignAndCreative')->once()->andReturn($advertiserWithCampAndCrea);
        App::instance('AdvertiserRepositoryInterface', $mock);

        var_dump($advertiserWithCampAndCrea);*/

        $response = $this->call('GET', 'v1/campaigns');
        $this->assertResponseOk();

        $this->assertResponseStatusEqual(0, $response);
    }

    function testByCoordinates() 
    {
        $response = $this->call('GET', 'v1/campaigns/52.3673448/-1.5780009');
        $this->assertResponseOk();

        $this->assertResponseStatusEqual(0, $response);
    }

    function testByCoordinatesBadParams() 
    {
        $response = $this->call('GET', 'v1/campaigns/0.0/0.0');
        $this->assertResponseOk();

        $this->assertResponseStatusNotEqual(0, $response);
    }

}