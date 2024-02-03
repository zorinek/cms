<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @persistent */  
    public $locale;
    
    public function beforeRender(): void {
        
        $this->template->locale = $this->locale;
    }
}
