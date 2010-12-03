<?php

namespace OpenIDForm;

use Nette;
use Nette\Debug;
use Nette\Application\AppForm;

class OpenIDForm extends Nette\Application\Control
{
	const OID_FIELD = 'openid_identifier';
	const PROCESS_SIGNAL = 'process';

	public $onCancel;
	public $onInvalid;
	public $onValid;

	protected $openid;

	public function __construct( Nette\IComponentContainer $parent = NULL, $name = NULL )
	{
			parent::__construct( $parent, $name );

			$this->openid = new \LightOpenID;
	}

	public function setRequired( $required ) {
		if ( is_array( $required ) ) {
			$this->openid->required = array_merge(
					 $this->openid->required,
					 $required
			);
		}
		else {
			$this->openid->required[] = $required;
		}
	}

	public function setOptional( $optional ) {
		if ( is_array( $optional ) ) {
			$this->openid->optional = array_merge(
					 $this->openid->optional,
					 $optional
			);
		}
		else {
			$this->openid->optional[] = $optional;
		}
	}

	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/default.phtml');
		$template->render();
	}

	public function handleProcess() {
		$this->openid->returnUrl = $this->returnUrl( self::PROCESS_SIGNAL );
		if( $this->openid->mode == 'cancel') {
			$this->onCancel();
		} elseif( $this->openid->mode ) {
			if ( $this->openid->validate() ) {
				$this->onValid(
					$this->openid->identity,
					$this->openid->getAttributes()
				);
			}
		}
	}

	public function createComponentIdentifierForm() {
		$form = new AppForm;
		$form->addText( self::OID_FIELD, 'Sign in with OpenID:')
			->addRule( Nette\Forms\Form::FILLED, 'Please fill in your OpenID!' );
		$form->addSubmit( 'login', 'Login' );
		$form->onSubmit[] = array( $this, 'processIdentifier' );
		return $form;
	}

	public function processIdentifier( AppForm $form )
	{
		$values = $form->values;
		$this->openid->identity = $values[ self::OID_FIELD ];
		$this->openid->returnUrl = $this->returnUrl( self::PROCESS_SIGNAL );
		
		header( 'Location: ' . $this->openid->authUrl() );
		exit();
	}

	protected function returnUrl( $signal ) {
		return $this->openid->trustRoot .
			$this->getParent()->link( $this->getName() . ':' . $signal . '!' );
	}
}
