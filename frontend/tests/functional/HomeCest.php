<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;

class HomeCest
{
    public function checkOpen(FunctionalTester $I)
    {
        $I->amOnRoute('site/index');
        $I->see('House 2 House');
        $I->seeLink('About');
        $I->click('About');
        $I->see('Running on php 8.2');
    }
}