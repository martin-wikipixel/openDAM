<?php

/**
 * Base project form.
 * 
 * @package    wikipixel
 * @subpackage form
 * @author     Your name here 
 * @version    SVN: $Id: BaseForm.class.php 20147 2009-07-13 11:46:57Z FabianLange $
 */
class BaseForm extends sfFormSymfony
{
	public function getErrors()
	{
		$errors = array();

		// individual widget errors
		foreach ($this as $form_field)
		{
			if ($form_field->hasError())
			{
				$error_obj = $form_field->getError();
				if ($error_obj instanceof sfValidatorErrorSchema)
				{
					foreach ($error_obj->getErrors() as $error)
					{
						// if a field has more than 1 error, it'll be over-written
						$errors[$form_field->getName()] = $error->getMessage().(sfConfig::get("sf_environment") == "dev" ? " (field : ".$form_field->getName().")" : "");
					}
				}
				else
				{
					$errors[$form_field->getName()] = $error_obj->getMessage().(sfConfig::get("sf_environment") == "dev" ? " (field : ".$form_field->getName().")" : "");
				}
			}
		}

		// global errors
		foreach ($this->getGlobalErrors() as $validator_error)
		{
			$errors[] = $validator_error->getMessage();
		}

		return $errors;
	}
}
