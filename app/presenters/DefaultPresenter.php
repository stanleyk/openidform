<?php

/**
 * OpenID Form Demo
 *
 * @copyright  Copyright (c) 2010 Stanislav Kocanda
 */

use \Nette;
use \Nette\Debug;
use \Nette\Environment;
use Nette\Application\AppForm;
use \OpenIDForm;

/**
 * Default presenter.
 *
 * @author     Stanislav Kocanda
 */
final class DefaultPresenter extends Nette\Application\Presenter
{
	const AX_NICKNAME = 'namePerson/friendly';

	public function renderLogged() {
		$user = Environment::getUser();
		$this->template->user = $user->getIdentity();
	}

	public function renderRegister() {
		$oidsession = Environment::getSession( 'openid' );
		$nickname = '';
		if ( isset( $oidsession->attributes[ self::AX_NICKNAME ] ) ) {
			$nickname = $oidsession->attributes[ self::AX_NICKNAME ];
		}
		if ( ! isset( $oidsession->identity ) ) {
			throw Exception( 'Lost session!' );
		}
		$this->template->nickname = ( ! empty( $nickname ) ) ?
			$nickname : $oidsession->identity;
	}

	/**
	 * OpenID Form control factory.
	 * @return mixed
	 */
	public function createComponentOpenIDForm() {
		$oid = new \OpenIDForm\OpenIDForm();
		$oid->setRequired( self::AX_NICKNAME );
		$oid->onSignin[] = callback( $this, 'openIDSigned' );
		return $oid;
	}

	/**
	 * Successful login callback.
	 * @param string
	 * @param array
	 */
	public function openIDSigned( $identity, $attributes ) {
		try {
            $this->user->login( array( $identity ) );
			$this->redirect( 'logged' );
		}
		catch ( Nette\Security\AuthenticationException $e ) {
			$oidsession = Environment::getSession( 'openid' );
			$oidsession->identity = $identity;
			$oidsession->attributes = $attributes;
			$this->redirect( 'register' );
		}
	}

	/**
	 * Create the register form
	 */
	public function createComponentRegisterForm() {
		$oidsession = Environment::getSession( 'openid' );
		$nickname = '';
		if ( isset( $oidsession->attributes[ self::AX_NICKNAME ] ) ) {
			$nickname = $oidsession->attributes[ self::AX_NICKNAME ];
		}
		if ( ! isset( $oidsession->identity ) ) {
			throw Exception( 'Lost session!' );
		}
		$form = new AppForm;
		$form->addText( 'nickname', 'Your nickname:')
			->addRule( Nette\Forms\Form::FILLED, 'Please fill in your nickname!' )
			->setDefaultValue( $nickname );
		$form->addHidden( 'openid' )
			->setValue( $oidsession->identity );
		$form->addSubmit( 'register', 'Register' );
		$form->onSubmit[] = array( $this, 'processRegister' );
		return $form;
	}

	/**
	 * The register form onSubmit function
	 * @param AppForm
	 */
	public function processRegister( AppForm $form ) {
		$values = $form->values;
		\Model\Users::register( $values );
		$this->user->login( $array( $values['openid'] ) );
		$this->redirect( 'logged' );
	}
}
