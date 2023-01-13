<?php

namespace Tests\Feature;

use Tests\TestCase;
use Webpatser\Uuid\Uuid;

class WalletTest extends TestCase
{
    /**
     * Test init wallet success
     *
     * @return string $token
     */
    public function testInitWallet()
    {
        $cid = Uuid::generate(4)->string;
        $data = [
            'customer_xid' => $cid,
        ];

        $response = $this->json('POST', '/api/v1/init', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'token',
                ],
            ]);

        return $response->getData()->data->token;
    }

    /**
     * Test init wallet fail empty input
     *
     * @return void
     */
    public function testInitWalletEmptyInput()
    {
        $data = [];

        $response = $this->json('POST', '/api/v1/init', $data);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'fail',
                'data' => [
                    "error" => [
                        "customer_xid" => [
                            "The customer xid field is required.",
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Test enable wallet success
     *
     * @return void
     */
    public function testEnableWallet()
    {
        $token = $this->testInitWallet();

        $headers = [
            'Authorization' => 'Token ' . $token,
            'Accept' => 'application/json',
        ];

        $response = $this->json('POST', '/api/v1/wallet', [], $headers);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'wallet' => [
                        'id',
                        'status',
                        'owned_by',
                        'enabled_at',
                        'balance',
                    ],
                ],
            ]);
    }
}
