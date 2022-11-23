<?php

namespace EasyPost\Test;

use EasyPost\EasyPost;
use EasyPost\User;
use VCR\VCR;

class UserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Setup the testing environment for this file.
     */
    public static function setUpBeforeClass(): void
    {
        EasyPost::setApiKey(getenv('EASYPOST_PROD_API_KEY'));

        VCR::turnOn();
    }

    /**
     * Cleanup the testing environment once finished.
     */
    public static function tearDownAfterClass(): void
    {
        VCR::eject();
        VCR::turnOff();
    }

    /**
     * Test creating a child user.
     */
    public function testCreate()
    {
        VCR::insertCassette('users/create.yml');

        $user = User::create([
            'name' => 'Test User',
        ]);

        $this->assertInstanceOf('\EasyPost\User', $user);
        $this->assertStringMatchesFormat('user_%s', $user->id);
        $this->assertEquals('Test User', $user->name);

        $user->delete(); // Delete the user once done so we don't pollute with hundreds of child users
    }

    /**
     * Test retrieving a user.
     */
    public function testRetrieve()
    {
        VCR::insertCassette('users/retrieve.yml');

        $authenticatedUser = User::retrieve_me();

        $user = User::retrieve($authenticatedUser['id']);

        $this->assertInstanceOf('\EasyPost\User', $user);
        $this->assertStringMatchesFormat('user_%s', $user->id);
    }

    /**
     * Test retrieving the authenticated user.
     */
    public function testRetrieveMe()
    {
        VCR::insertCassette('users/retrieveMe.yml');

        $user = User::retrieve_me();

        $this->assertInstanceOf('\EasyPost\User', $user);
        $this->assertStringMatchesFormat('user_%s', $user->id);
    }

    /**
     * Test updating the authenticated user.
     */
    public function testUpdate()
    {
        VCR::insertCassette('users/update.yml');

        $user = User::retrieve_me();

        $testPhone = '5555555555';

        $user->phone = $testPhone;
        $user->save();

        $this->assertInstanceOf('\EasyPost\User', $user);
        $this->assertStringMatchesFormat('user_%s', $user->id);
        $this->assertEquals($testPhone, $user->phone);
    }

    /**
     * Test deleting a child user.
     */
    public function testDelete()
    {
        VCR::insertCassette('users/delete.yml');

        $user = User::create([
            'name' => 'Test User',
        ]);

        $response = $user->delete();

        $this->assertNotNull($response);
    }

    /**
     * Test retrieving all API keys.
     */
    public function testAllApiKeys()
    {
        VCR::insertCassette('users/all_api_keys.yml');

        $user = User::retrieve_me();

        $apiKeys = $user::all_api_keys();

        $this->assertNotNull($apiKeys['keys']);
        $this->assertNotNull($apiKeys['children']);

        // TODO: When the output of this function is fixed, swap the tests for the below
        // $this->assertContainsOnlyInstancesOf('\EasyPost\ApiKey', $apiKeys);
        // foreach ($apiKeys['children'] as $child) {
        //     $this->assertContainsOnlyInstancesOf('\EasyPost\ApiKey', $child['keys']);
        // }
    }

    /**
     * Test retrieving the authenticated user's API keys.
     */
    public function testAuthenticatedUserApiKeys()
    {
        VCR::insertCassette('users/authenticated_user_api_keys.yml');

        $user = User::retrieve_me();

        $apiKeys = $user->api_keys();

        $this->assertNotNull($apiKeys['production']);
        $this->assertNotNull($apiKeys['test']);

        // TODO: When the output of this function is fixed, swap the tests for the below
        // $this->assertContainsOnlyInstancesOf('\EasyPost\ApiKey', $apiKeys);
    }

    /**
     * Test retrieving the authenticated user's API keys.
     */
    public function testChildUserApiKeys()
    {
        VCR::insertCassette('users/child_user_api_keys.yml');

        $user = User::create([
            'name' => 'Test User',
        ]);
        $childUser = User::retrieve($user->id);

        $apiKeys = $childUser->api_keys();

        $this->assertNotNull($apiKeys['production']);
        $this->assertNotNull($apiKeys['test']);

        // TODO: When the output of this function is fixed, swap the tests for the below
        // $this->assertContainsOnlyInstancesOf('\EasyPost\ApiKey', $apiKeys);

        $user->delete(); // Delete the user once done so we don't pollute with hundreds of child users
    }

    /**
     * Test updating the authenticated user's Brand.
     */
    public function testUpdateBrand()
    {
        VCR::insertCassette('users/brand.yml');

        $user = User::retrieve_me();

        $color = '#123456';

        $brand = $user->update_brand([
            'color' => $color,
        ]);

        $this->assertInstanceOf('\EasyPost\Brand', $brand);
        $this->assertStringMatchesFormat('brd_%s', $brand->id);
        $this->assertEquals($color, $brand->color);
    }
}
