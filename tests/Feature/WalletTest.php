<?php

namespace Tests\Feature;

use Tests\TestCase;

class WalletTest extends TestCase
{
    /**
     * Test init wallet success
     *
     * @return \Illuminate\Http\Response
     */
    public function testInitWallet()
    {
        $data = [
            'customer_xid' => 'ea0212d3-abd6-406f-8c67-868e814a2436',
        ];

        $response = $this->json('POST', '/api/v1/init', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'token' => 'd0d7e421453a1d6b6f54cf1a352cb6359a0b7a0711e77a5aa81384a9fa5a3516',
                ],
            ]);
    }

    /**
     * Test init wallet fail empty input
     *
     * @return \Illuminate\Http\Response
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
}
