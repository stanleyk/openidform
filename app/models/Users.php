<?php

namespace Model;
use \Nette;
use \Nette\Security;

class Users extends Nette\Object implements Security\IAuthenticator
{
    private $users_table = 'users';
    private $ids_table   = 'openids';

    public function authenticate(array $credentials) {

        $openid = $credentials[self::USERNAME];

        $row = \dibi::select('*')
                ->from($this->users_table)
                ->innerJoin($this->ids_table)
                ->where('openid=%s', $openid)
                ->fetch();

        if (!$row) {
            throw new Security\AuthenticationException(
                                'No user', self::IDENTITY_NOT_FOUND);
        }

        return new Security\Identity($row->id, NULL, $row);
    }
}

