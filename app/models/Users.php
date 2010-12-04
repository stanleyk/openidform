<?php

namespace Model;

use dibi;
use Nette;
use Nette\Security;

class Users extends Nette\Object implements Security\IAuthenticator
{
    const USERS_TABLE = 'users';
    const IDS_TABLE = 'openids';

    public function authenticate( array $credentials ) {

        $openid = $credentials[ self::USERNAME ];

        $row = dibi::select('*')
                ->from( self::USERS_TABLE )
                ->innerJoin( self::IDS_TABLE )
                ->on('users.id = openids.user_id')
                ->where( 'openid=%s', $openid )
                ->fetch();

        if (!$row) {
            throw new Security\AuthenticationException(
                                'No user', self::IDENTITY_NOT_FOUND);
        }

        return new Security\Identity($row->id, NULL, $row);
    }

	public static function register( $values ) {
		$data = array(
			'nickname' => $values[ 'nickname' ],
		);
		dibi::insert( self::USERS_TABLE, $data )->execute();
		$userid = dibi::getInsertId();
		$data = array(
			'openid' => $values[ 'openid' ],
			'user_id' => $userid,
		);
		dibi::insert( self::IDS_TABLE, $data )->execute();
	}
}
