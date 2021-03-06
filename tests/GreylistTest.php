<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GreylistTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = [
        'sqlite_sqlgrey',
    ];

    public function test_greylist_is_displayed()
    {
        $greylisted = factory(\SQLgreyGUI\Models\Greylist::class, 20)->create();

        $this->actingAsAdmin()
            ->visit(action('GreylistController@index'))
            ->see('Greylist');

        foreach ($greylisted as $item) {
            $this->see($item->getSenderName())
                ->see($item->getSenderDomain())
                ->see($item->getSource())
                ->see($item->getRecipient())
                ->see($item->getFirstSeen())
                ->see(cval($item));
        }

        $this->see('delete selected')
            ->see('move selected to whitelist')
            ->see('delete by date');
    }

    public function test_entries_can_be_deleted()
    {
        $greylisted = factory(\SQLgreyGUI\Models\Greylist::class, 5)->create();

        $data = [];

        foreach ($greylisted as $entry) {
            $data[] = cval($entry);
        }

        $this->actingAsAdmin()
            ->post(action('GreylistController@delete'), [
                'greylist' => $data,
            ]);

        foreach ($greylisted as $entry) {
            $this->notSeeInDatabase('connect', [
                'sender_name' => $entry->getSenderName(),
                'sender_domain' => $entry->getSenderDomain(),
                'src' => $entry->getSource(),
                'rcpt' => $entry->getRecipient(),
            ], 'sqlite_sqlgrey');
        }
    }

    public function test_entries_can_be_moved_to_whitelist()
    {
        $entry = factory(\SQLgreyGUI\Models\Greylist::class)->create();

        $this->actingAsAdmin()
            ->seeInDatabase('connect', [
                'sender_name' => $entry->getSenderName(),
                'sender_domain' => $entry->getSenderDomain(),
                'src' => $entry->getSource(),
                'rcpt' => $entry->getRecipient(),
                'first_seen' => $entry->getFirstSeen(),
            ], 'sqlite_sqlgrey')
            ->post(action('GreylistController@move'), [
                'greylist' => [
                    cval($entry),
                ],
            ])
            ->notSeeInDatabase('connect', [
                'sender_name' => $entry->getSenderName(),
                'sender_domain' => $entry->getSenderDomain(),
                'src' => $entry->getSource(),
                'rcpt' => $entry->getRecipient(),
            ], 'sqlite_sqlgrey')
            ->seeInDatabase('from_awl', [
                'sender_name' => $entry->getSenderName(),
                'sender_domain' => $entry->getSenderDomain(),
                'src' => $entry->getSource(),
                'first_seen' => $entry->getFirstSeen(),
                'last_seen' => $entry->getFirstSeen(),
            ], 'sqlite_sqlgrey');
    }

    public function test_entries_can_be_deleted_by_date()
    {
        $greylisted_before = factory(\SQLgreyGUI\Models\Greylist::class, 7)->create([
            'first_seen' => Carbon::now()->subYear(),
        ]);

        $greylisted_after = factory(\SQLgreyGUI\Models\Greylist::class, 5)->create([
            'first_seen' => Carbon::now()->addYear(),
        ]);

        $this->actingAsAdmin()
            ->visit(action('GreylistController@index'));

        foreach ($greylisted_before as $entry) {
            $this->seeInDatabase('connect', [
                'sender_name' => $entry->getSenderName(),
                'sender_domain' => $entry->getSenderDomain(),
                'src' => $entry->getSource(),
                'rcpt' => $entry->getRecipient(),
            ], 'sqlite_sqlgrey')
                ->see($entry->getSenderName())
                ->see($entry->getSenderDomain())
                ->see($entry->getSource())
                ->see($entry->getRecipient())
                ->see($entry->getFirstSeen());
        }

        foreach ($greylisted_after as $entry) {
            $this->seeInDatabase('connect', [
                'sender_name' => $entry->getSenderName(),
                'sender_domain' => $entry->getSenderDomain(),
                'src' => $entry->getSource(),
                'rcpt' => $entry->getRecipient(),
            ], 'sqlite_sqlgrey')
                ->see($entry->getSenderName())
                ->see($entry->getSenderDomain())
                ->see($entry->getSource())
                ->see($entry->getRecipient())
                ->see($entry->getFirstSeen());
        }

        $delete_date = Carbon::now();

        $this->type($delete_date->format('Y-m-d H:i:s'), 'delete_by_date')
            ->press('delete')
            ->see('deleted entries older than '.$delete_date->toDateTimeString());

        foreach ($greylisted_before as $entry) {
            $this->notSeeInDatabase('connect', [
                'sender_name' => $entry->getSenderName(),
                'sender_domain' => $entry->getSenderDomain(),
                'src' => $entry->getSource(),
                'rcpt' => $entry->getRecipient(),
            ], 'sqlite_sqlgrey');
        }

        foreach ($greylisted_after as $entry) {
            $this->seeInDatabase('connect', [
                'sender_name' => $entry->getSenderName(),
                'sender_domain' => $entry->getSenderDomain(),
                'src' => $entry->getSource(),
                'rcpt' => $entry->getRecipient(),
            ], 'sqlite_sqlgrey');
        }
    }
}
