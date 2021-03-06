<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class OptOutTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = [
        'sqlite_sqlgrey',
    ];

    public function test_emails_are_displayed()
    {
        $emails = factory(\SQLgreyGUI\Models\OptOutEmail::class, 7)->create();

        $this->actingAsAdmin()
            ->visit(action('OptOutController@showEmails'));

        foreach ($emails as $email) {
            $this->see($email->getEmail())
                ->see(cval($email));
        }
    }

    public function test_add_email()
    {
        $this->actingAsAdmin()
            ->visit(action('OptOutController@showEmails'))
            ->type('foo@bar.baz', 'email')
            ->press('save')
            ->see('foo@bar.baz has been added')
            ->seeInDatabase('optout_email', [
                'email' => 'foo@bar.baz',
            ], 'sqlite_sqlgrey');
    }

    public function test_duplicate_emails_are_ignored()
    {
        $email = factory(\SQLgreyGUI\Models\OptOutEmail::class)->create();

        $this->actingAsAdmin()
            ->visit(action('OptOutController@showEmails'))
            ->type($email->getEmail(), 'email')
            ->press('save')
            ->see($email->getEmail().' is already present');
    }

    public function test_delete_email()
    {
        $email = factory(\SQLgreyGUI\Models\OptOutEmail::class)->create();

        $this->actingAsAdmin()
            ->visit(action('OptOutController@showEmails'))
            ->submitForm('delete selected', [
                'emails' => [
                    cval($email),
                ],
            ])
            ->see('deleted the following entries:')
            ->notSeeInDatabase('optout_email', [
                'email' => $email->getEmail(),
            ], 'sqlite_sqlgrey');
    }

    public function test_domains_are_displayed()
    {
        $domains = factory(\SQLgreyGUI\Models\OptOutDomain::class, 7)->create();

        $this->actingAsAdmin()
            ->visit(action('OptOutController@showDomains'));

        foreach ($domains as $domain) {
            $this->see($domain->getDomain())
                ->see(cval($domain));
        }
    }

    public function test_add_domain()
    {
        $this->actingAsAdmin()
            ->visit(action('OptOutController@showDomains'))
            ->type('bar.baz', 'domain')
            ->press('save')
            ->see('bar.baz has been added')
            ->seeInDatabase('optout_domain', [
                'domain' => 'bar.baz',
            ], 'sqlite_sqlgrey');
    }

    public function test_duplicate_domains_are_ignored()
    {
        $domain = factory(\SQLgreyGUI\Models\OptOutDomain::class)->create();

        $this->actingAsAdmin()
            ->visit(action('OptOutController@showDomains'))
            ->type($domain->getDomain(), 'domain')
            ->press('save')
            ->see($domain->getDomain().' is already present');
    }

    public function test_delete_domain()
    {
        $domain = factory(\SQLgreyGUI\Models\OptOutDomain::class)->create();

        $this->actingAsAdmin()
            ->visit(action('OptOutController@showDomains'))
            ->submitForm('delete selected', [
                'domains' => [
                    cval($domain),
                ],
            ])
            ->see('deleted the following entries:')
            ->notSeeInDatabase('optout_domain', [
                'domain' => $domain->getDomain(),
            ], 'sqlite_sqlgrey');
    }
}
