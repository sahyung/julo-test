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
     * @return string $token
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

        return $token;
    }

    /**
     * Test enable wallet fail already enabled
     *
     * @return void
     */
    public function testWalletAlreadyEnabled()
    {
        $token = $this->testInitWallet();

        $headers = [
            'Authorization' => 'Token ' . $token,
            'Accept' => 'application/json',
        ];

        $response = $this->json('POST', '/api/v1/wallet', [], $headers);
        $response = $this->json('POST', '/api/v1/wallet', [], $headers);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'fail',
                'data' => [
                    "error" => "Already enabled",
                ],
            ]);
    }

    /**
     * Test view my wallet balance success
     *
     * @return void
     */
    public function testViewWallet()
    {
        $token = $this->testEnableWallet();

        $headers = [
            'Authorization' => 'Token ' . $token,
            'Accept' => 'application/json',
        ];

        $response = $this->json('GET', '/api/v1/wallet', [], $headers);

        $response->assertStatus(200)
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

    /**
     * Test view my wallet balance disabled
     *
     * @return void
     */
    public function testViewWalletDisabled()
    {
        $token = $this->testInitWallet();

        $headers = [
            'Authorization' => 'Token ' . $token,
            'Accept' => 'application/json',
        ];

        $response = $this->json('GET', '/api/v1/wallet', [], $headers);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'fail',
                'data' => [
                    "error" => "Wallet disabled",
                ],
            ]);
    }

    /**
     * Test view my wallet transactions success
     *
     * @return void
     */
    public function testViewWalletTransactions()
    {
        $deposit = $this->testWalletDeposit();
        $token = $deposit['token'];

        $headers = [
            'Authorization' => 'Token ' . $token,
            'Accept' => 'application/json',
        ];

        $response = $this->json('GET', '/api/v1/wallet/transactions', [], $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'transactions' => [
                        [
                            'id',
                            'status',
                            'transacted_at',
                            'type',
                            'amount',
                            'reference_id',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Test view my wallet transactions disabled
     *
     * @return void
     */
    public function testViewWalletTransactionsDisabled()
    {
        $token = $this->testInitWallet();

        $headers = [
            'Authorization' => 'Token ' . $token,
            'Accept' => 'application/json',
        ];

        $response = $this->json('GET', '/api/v1/wallet/transactions', [], $headers);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'fail',
                'data' => [
                    "error" => "Wallet disabled",
                ],
            ]);
    }

    /**
     * Test add virtual money to my wallet success
     *
     * @return array $data ['token' => string $token, 'reference_id' => string $reffId]
     */
    public function testWalletDeposit()
    {
        $token = $this->testEnableWallet();

        $headers = [
            'Authorization' => 'Token ' . $token,
            'Accept' => 'application/json',
        ];

        $data = [
            'amount' => 100000,
            'reference_id' => Uuid::generate(4)->string,
        ];

        $response = $this->json('POST', '/api/v1/wallet/deposits', $data, $headers);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'deposit' => [
                        'id',
                        'deposited_by',
                        'status',
                        'deposited_at',
                        'amount',
                        'reference_id',
                    ],
                ],
            ]);

        return [
            'token' => $token,
            'reference_id' => $data['reference_id'],
        ];
    }

    /**
     * Test add virtual money to my wallet fail wallet disabled
     *
     * @return void
     */
    public function testWalletDepositDisabled()
    {
        $token = $this->testInitWallet();

        $headers = [
            'Authorization' => 'Token ' . $token,
            'Accept' => 'application/json',
        ];

        $data = [
            'amount' => 100000,
            'reference_id' => Uuid::generate(4)->string,
        ];

        $response = $this->json('POST', '/api/v1/wallet/deposits', $data, $headers);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'fail',
                'data' => [
                    "error" => "Wallet disabled",
                ],
            ]);
    }

    /**
     * Test add virtual money to my wallet fail invalid amount
     *
     * @return void
     */
    public function testWalletDepositInvalidAmount()
    {
        $token = $this->testEnableWallet();

        $headers = [
            'Authorization' => 'Token ' . $token,
            'Accept' => 'application/json',
        ];

        $data = [
            'amount' => 0,
            'reference_id' => Uuid::generate(4)->string,
        ];

        $response = $this->json('POST', '/api/v1/wallet/deposits', $data, $headers);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'fail',
                'data' => [
                    "error" => [
                        "amount" => [],
                    ],
                ],
            ]);
    }

    /**
     * Test add virtual money to my wallet fail invalid reference_id
     *
     * @return void
     */
    public function testWalletDepositInvalidReffId()
    {
        $deposit = $this->testWalletDeposit();
        $token = $deposit['token'];

        $headers = [
            'Authorization' => 'Token ' . $token,
            'Accept' => 'application/json',
        ];

        $data = [
            'amount' => 0,
            'reference_id' => $deposit['reference_id'],
        ];

        $response = $this->json('POST', '/api/v1/wallet/deposits', $data, $headers);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'fail',
                'data' => [
                    "error" => [
                        "reference_id" => [],
                    ],
                ],
            ]);
    }

    /**
     * Test use virtual money from my wallet success
     *
     * @return string $token
     */
    public function testWalletWithdrawal()
    {
        $deposit = $this->testWalletDeposit();
        $token = $deposit['token'];

        $headers = [
            'Authorization' => 'Token ' . $token,
            'Accept' => 'application/json',
        ];

        $data = [
            'amount' => 100000,
            'reference_id' => Uuid::generate(4)->string,
        ];

        $response = $this->json('POST', '/api/v1/wallet/withdrawals', $data, $headers);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    "withdrawal" => [
                        'id',
                        'withdrawn_by',
                        'status',
                        'withdrawn_at',
                        'amount',
                        'reference_id',
                    ],
                ],
            ]);

        return $token;
    }

    /**
     * Test use virtual money from my wallet fail wallet disabled
     *
     * @return void
     */
    public function testWalletWithdrawalDisabled()
    {
        $token = $this->testInitWallet();

        $headers = [
            'Authorization' => 'Token ' . $token,
            'Accept' => 'application/json',
        ];

        $data = [
            'amount' => 100000,
            'reference_id' => Uuid::generate(4)->string,
        ];

        $response = $this->json('POST', '/api/v1/wallet/withdrawals', $data, $headers);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'fail',
                'data' => [
                    "error" => "Wallet disabled",
                ],
            ]);
    }

    /**
     * Test use virtual money from my wallet fail invalid amount
     *
     * @return void
     */
    public function testWalletWithdrawalInvalidAmount()
    {
        $token = $this->testEnableWallet();

        $headers = [
            'Authorization' => 'Token ' . $token,
            'Accept' => 'application/json',
        ];

        $data = [
            'amount' => 0,
            'reference_id' => Uuid::generate(4)->string,
        ];

        $response = $this->json('POST', '/api/v1/wallet/withdrawals', $data, $headers);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'fail',
                'data' => [
                    "error" => [
                        "amount" => [],
                    ],
                ],
            ]);
    }

    /**
     * Test add virtual money to my wallet fail invalid reference_id
     *
     * @return void
     */
    public function testWalletWithdrawalInvalidReffId()
    {
        $deposit = $this->testWalletDeposit();
        $token = $deposit['token'];

        $headers = [
            'Authorization' => 'Token ' . $token,
            'Accept' => 'application/json',
        ];

        $data = [
            'amount' => 0,
            'reference_id' => $deposit['reference_id'],
        ];

        $response = $this->json('POST', '/api/v1/wallet/withdrawals', $data, $headers);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'fail',
                'data' => [
                    "error" => [
                        "reference_id" => [],
                    ],
                ],
            ]);
    }
}
