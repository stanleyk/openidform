<?php

/**
 * OpenID Authentication Form
 *
 * Copyright (c) 2010 Stanislav Kocanda (http://www.stanleyk.net)
 *
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 *
 */

namespace OpenIDForm;

use Nette;
use Nette\Debug;
use Nette\Application\AppForm;

/**
 * OpenID Authentication Form control
 *
 * @author     Stanislav Kocanda
 */
class OpenIDForm extends Nette\Application\Control
{
	/** form field name */
	const OID_FIELD = 'openid_identifier';

	/** name of the signal processing OP redirection */
	const PROCESS_SIGNAL = 'process';

	/** @var array of function(void);
	 *  Occurs when the user cancels OpenID authentication */
	public $onCancel;

	/** @var array of function(void);
	 *  Occurs when the user authentication fails */
	public $onInvalid;

	/** @var array of function($identity, $attributes);
	 *  Occurs when the user authentication succeeds */
	public $onValid;

	/** @var LightOpenID */
	protected $openid;

	/**
	 * Component constructor.
	 * @param  Nette\IComponentContainer
	 * @param  string
	 */
	public function __construct(
		Nette\IComponentContainer $parent = NULL, $name = NULL
	) {
			parent::__construct( $parent, $name );

			$this->openid = new \LightOpenID;
	}

	/**
	 * Sets required fields in the AX format.
	 * Accepts either string or an array of strings
	 * @param  mixed 
	 */
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

	/**
	 * Sets optional fields in the AX format.
	 * Accepts either string or an array of strings
	 * @param  mixed 
	 */
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

	/**
	 * Component rendering
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(__DIR__ . '/default.phtml');
		$template->render();
	}

	/**
	 * Handle the process! signal.
	 * This signal occurs after a successful redirection back from the OP
	 */
	public function handleProcess() {
		$this->openid->returnUrl = $this->returnUrl( self::PROCESS_SIGNAL );
		if( $this->openid->mode == 'cancel') {
			$this->onCancel();
		}
		elseif( $this->openid->mode ) {
			if ( $this->openid->validate() ) {
				$this->onValid(
					$this->openid->identity,
					$this->openid->getAttributes()
				);
			}
			else {
				$this->onInvalid();
			}
		}
	}

	/**
	 * Create the form itself
	 */
	public function createComponentIdentifierForm() {
		$form = new AppForm;
		$form->addText( self::OID_FIELD, 'Sign in with OpenID:')
			->addRule( Nette\Forms\Form::FILLED, 'Please fill in your OpenID!' );
		$form->addSubmit( 'login', 'Login' );
		$form->onSubmit[] = array( $this, 'processIdentifier' );
		return $form;
	}

	/**
	 * The form onSubmit function
	 * @param AppForm
	 */
	public function processIdentifier( AppForm $form ) {
		$values = $form->values;
		$this->openid->identity = $values[ self::OID_FIELD ];
		$this->openid->returnUrl = $this->returnUrl( self::PROCESS_SIGNAL );
		
		header( 'Location: ' . $this->openid->authUrl() );
		exit();
	}

	/**
	 * Compose the return URL for the OpenId request.
	 * Includes the processing signal call.
	 * @param string
	 */
	protected function returnUrl( $signal ) {
		return $this->openid->trustRoot .
			$this->getParent()->link( $this->getName() . ':' . $signal . '!' );
	}
}
