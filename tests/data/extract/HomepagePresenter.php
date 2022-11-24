<?php declare(strict_types = 1);

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;
use Nette\Localization\Translator;

class HomepagePresenter extends Presenter
{

	protected Translator $translator;

	public function actionDefault(): void
	{
		$this->translator->translate('Xxx');
	}

	protected function createComponent(string $name): IComponent
	{
		$form = new Form($this, $name);

		$form->addProtection('Invalid token');
		$form->addError('Error!');

		return $form;
	}

}
