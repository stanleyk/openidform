<?php

/**
 * OpenID Form Demo
 *
 * @copyright  Copyright (c) 2010 Stanislav Kocanda
 */


/**
 * Default presenter.
 *
 * @author     Stanislav Kocanda
 */

use \Nette;
use \OpenIDForm;

final class DefaultPresenter extends Nette\Application\Presenter
{
	public function createComponentOpenIDForm()
	{
		$oid = new \OpenIDForm\OpenIDForm();
		$oid->setRequired( 'contact/email' );
		$oid->setOptional( 'namePerson/friendly' );
		$oid->onValid[] = callback( $this, 'validOpenID' );
		$oid->onInvalid[] = callback( $this, 'invalidOpenID' );
		$oid->onCancel[] = callback( $this, 'cancelledOpenID' );
		return $oid;
	}

	public function validOpenID( $identity, $attributes ) {
		$this->template->msg = 
			'You have successfuly logged in as ' . $identity;
		$this->template->attributes = $attributes;
	}

	public function invalidOpenID() {
		$this->template->msg = 'You have not logged in!';
	}

	public function cancelledOpenID() {
		$this->template->msg = 'You have cancelled logging in!';
	}
}
