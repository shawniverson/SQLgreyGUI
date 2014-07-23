<?php

namespace Bausch\Repositories;

use Bausch\Models\AwlDomain as Domain;

class AwlDomainRepositoryEloquent implements AwlDomainRepositoryInterface {

    public function findAll() {
        $data = Domain::orderBy('sender_domain', 'asc')->get();

        return $data;
    }

    public function instance($data = array()) {
        return new Domain($data);
    }

    public function destroy(Domain $domain) {
        return Domain::where('sender_domain', $domain->getSenderDomain())
                        ->where('src', $domain->getSource())
                        ->delete();
    }

}
