<?php

/**
 * OpenID Form Demo
 *
 * @copyright  Copyright (c) 2010 Stanislav Kocanda
 */

use \Nette;
use \OpenIDForm;

/**
 * Default presenter.
 *
 * @author     Stanislav Kocanda
 */
final class DefaultPresenter extends Nette\Application\Presenter
{
	/**
	 * OpenID Form control factory.
	 * @return mixed
	 */
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

	/**
	 * Successful login callback.
	 * @param string
	 * @param array
	 */
	public function validOpenID( $identity, $attributes ) {
		$this->template->msg = 
			'You have successfuly logged in as ' . $identity;
		$this->template->attributes = $attributes;
	}

	/**
	 * Failed login callback.
	 */
	public function invalidOpenID() {
		$this->template->msg = 'You have not logged in!';
	}

	/**
	 * Cancelled login callback.
	 */
	public function cancelledOpenID() {
		$this->template->msg = 'You have cancelled logging in!';
	}
}
