<?php

/**
 * Class AdminAffiliateCest
 * @group addons_admin
 */
class AdminAffiliateCest
{
    private $current_page; 
    private $customer_ref_id;
    private $customer_id;
    private $user_id;
    private $plan_id;
    private $order_id;
    private $promo_id;
    private $plan_name;
    private $referal_name;
    private $affiliate_admin_url;
    private $referal_admin_url;
    private $referal_link;
    private $customer_link;
    private $email_user;
    private $promo_code;

    public function _before(AcceptanceTester $I, $scenario)
    {
        $config = $I->getAcceptanceSuiteConfig('WebDriver');
        if (empty($config)) {
            $scenario->skip('WebDriver required for this test');
        }

        $options = $I->getOptions();
        $this->admin_index = '/' . $options['admin_index'];
        $this->customer_index = '/' . $options['customer_index'];
        $this->payment_id = $options['phone_ordering_payment_id'];

        $id_params = [
            'admin_domain',
            'product_id'
        ];

        foreach ($id_params as $param) {
            $this->$param = !empty($options[$param]) ? $options[$param] : 0;
        }
    }

    public function _after(AcceptanceTester $I)
    {
        $I->dontSeeErrors($this->current_page);
    }

    public function ChangeOptionsModule(AcceptanceTester $I, $scenario)
    {
        $I->wantTo('Change options Module');
        $I->amLoggedInAs('admin');
        $I->saveSessionSnapshot('login_admin');
        $this->current_page = $this->admin_index . '?dispatch=addons.manage&source=third_party';
        $I->amOnPage($this->current_page);
        $I->clickElement('tr[id*="addon_sd_affiliateinstalled"] a[data-ca-external-click-id*="sd_affiliateinstalled"]');
        $I->waitForElement('//input[contains(@id, "all_customers")]', 5);
        $I->checkOption('//input[contains(@id, "all_customers")]');
        $I->pressKey('input[id*="addon_option_sd_affiliate_lkey"]',WebDriverKeys::PAGE_DOWN);
        $I->waitForElement('input[id *= "approval_commissions"]', 5);
        $I->checkOption('input[id *= "approval_commissions"]');
        $I->clickElement('input[name*="dispatch[addons.update]"]');
    }

        public function RegistrationNewCustomer(AcceptanceTester $I, $scenario)
    {
        $I->wantTo('Registration new customer');
        $rand = rand(1000, 9999);
        $this->current_page = 'profiles-add';
        $I->amOnPage($this->current_page);
        $I->fillField('input[id*="email"]', 'customer' . $rand . '@example.com');
        $I->fillField('input[id*="password1"]', 'example');
        $I->fillField('input[id*="password2"]', 'example');
        $I->fillField('input[name*="user_data[firstname]"]', 'Customer' . $rand);
        $I->fillField('input[name*="user_data[lastname]"]', 'Test');
        $I->fillField('input[name*="user_data[phone]"]', '+11111111111');
        $I->clickElement('button[name*="dispatch[profiles.update]"]');
        $I->waitForElement('h1[class*="ty-mainbox-title"]', 5);
        $I->seeInCurrentUrl('?dispatch=profiles.success_add');
        $this->current_page = $this->customer_index . '?dispatch=affiliate_plans.list';
        $I->amOnPage($this->current_page);
        $this->customer_link = $I->grabMultiple('input[class*="ty-input-display"]','value');
    //БУДЕТ НОВЫЙ ID у инпута id="referal_link"
        preg_match('/aff_id=(\d+)/', end($this->customer_link),$found);
        $this->customer_id = $found[1];
        $I->saveSessionSnapshot('login_customer');
    }

    public function RegistrationReferalCustomer(AcceptanceTester $I, $scenario)
    {
        $I->wantTo('Registration referal customer');
        $rand = rand(1000, 9999);
        $I->amOnUrl(end($this->customer_link));
        $I->fillField('input[id*="email"]', 'customer_ref' . $rand . '@example.com');
        $I->fillField('input[id*="password1"]', 'example');
        $I->fillField('input[id*="password2"]', 'example');
        $I->fillField('input[name*="user_data[firstname]"]', 'Customer_ref' . $rand);
        $I->fillField('input[name*="user_data[lastname]"]', 'Test');
        $I->fillField('input[name*="user_data[phone]"]', '+11111111111');
        $I->clickElement('button[name*="dispatch[profiles.update]"]');
        $I->waitForElement('h1[class*="ty-mainbox-title"]', 5);
        $I->seeInCurrentUrl('?dispatch=profiles.success_add');
        $this->current_page = $this->admin_domain . $this->customer_index . '?dispatch=affiliate_plans.list';
        $I->amOnUrl($this->current_page);
        $link = $I->grabMultiple('input[class*="ty-input-display"]','value'); 
    //БУДЕТ НОВЫЙ ID у инпута id="referal_link"
        preg_match('/aff_id=(\d+)/',end($link),$found);
        $this->customer_ref_id = $found[1];
    }

        public function CheckBalanceForNewCustomer(AcceptanceTester $I, $scenario)
    {
        $I->wantTo('Check balance for new customer and creation tree customer');
        $I->loadSessionSnapshot('login_admin');
        $this->current_page = $this->admin_index . '?dispatch=reward_points.userlog&user_id=' . $this->customer_id;
        $I->amOnPage($this->current_page);
        $I->seeElement('//td[contains(text(),15)]');
    //БУДЕТ НОВЫЙ ID у td id="balance_points"

    }

    public function RegistrationNewAffiliate(AcceptanceTester $I, $scenario)
    {
        $I->wantTo('Registration new affiliate');
        $rand = rand(1000, 9999);
        $this->current_page = 'profiles-add';
        $I->amOnPage($this->current_page);
        $I->selectOption('select[id*="user_type"]', 'P');
        $I->fillField('input[id*="email"]', 'user' . $rand . '@example.com');
        $this->email_user = 'user' . $rand . '@example.com';
        $I->fillField('input[id*="password1"]', 'example');
        $I->fillField('input[id*="password2"]', 'example');
        $I->fillField('input[name*="user_data[firstname]"]', 'Affiliate' . $rand);
        $I->fillField('input[name*="user_data[lastname]"]', 'Test');
        $I->fillField('input[name*="user_data[phone]"]', '+11111111111');
        $I->clickElement('button[name*="dispatch[profiles.update]"]');
        $I->waitForElement('h1[class*="ty-mainbox-title"]', 5);
        $I->seeInCurrentUrl('?dispatch=profiles.success_add');
    //ЗДЕСЬ будем получать ID affiliate
        $I->saveSessionSnapshot('login_affiliate');
    }

    public function CheckAffiliateMenu(AcceptanceTester $I, $scenario)
    {
        $I->wantTo('Check affiliate menu');
        $I->loadSessionSnapshot('login_affiliate');
        $this->current_page = 'index.php?dispatch=affiliate_plans.list';
        $I->amOnPage($this->current_page);
        $I->dontSeeElementInDOM('div[class*="clearfix affiliate-plan-block"]');
    }

    public function CreatingPlanAffilate(AcceptanceTester $I, $scenario)
    {
        $I->loadSessionSnapshot('login_admin');
        $I->wantTo('Creating a plan affiliate');
        $this->current_page = $this->admin_index . '?dispatch=affiliate_plans.add';
        $I->amOnPage($this->current_page);
        $rand = rand(1000, 9999);
        $I->fillField('input[id*="elm_aff_plan_name"]', 'Example plan' . $rand);
        $this->plan_name = 'Example plan' . $rand;
        $I->fillField('input[id*="elm_aff_plan_min_payment"]', '10');
        $I->fillField('input[id*="elm_aff_plan_payout_types_show"]', '1');
        $I->fillField('input[id*="elm_aff_plan_payout_types_click"]', '2');
        $I->fillField('input[id*="elm_aff_plan_payout_types_click_ref"]', '3');
        $I->fillField('input[id*="elm_aff_plan_payout_types_sale"]', '4');
        $I->fillField('input[id*="elm_aff_plan_payout_types_new_customer"]', '5');
        $I->fillField('input[id*="elm_aff_plan_payout_types_new_partner"]', '6');
        $I->fillField('input[id*="elm_aff_plan_payout_types_use_coupon"]', '7');
        $I->clickElement('a[data-ca-dispatch*="dispatch[affiliate_plans.update]"]');
        $this->plan_id = $I->grabFromCurrentUrl('~.*plan_id=(\d*)~');
        $I->seeInCurrentUrl('affiliate_plans.update');
    }

    public function CreatingPromoCode(AcceptanceTester $I, $scenario)
    {
        $I->loadSessionSnapshot('login_admin');
        $I->wantTo('Creating a promo code');
        $this->current_page = $this->admin_index . '?dispatch=promotions.add&zone=cart';
        $I->amOnPage($this->current_page);
        $rand = rand(1000, 9999);
        $I->fillField('input[name*="promotion_data[name]"]', 'Example promo' . $rand);
        $I->clickElement('li[id*="conditions"]');
        $I->click('//a[contains(@onclick, "condition")and not(contains(@onclick, "cart"))]');
    // БУДЕТ НОВЫЙ ID у кнопки id= "add_condition"
        $I->selectOption('select[name*="[conditions]"]', 'coupon_code');
        $I->waitForElement('div[style*="display: none;"]', 5);
        $I->fillField('input[name*="[value]"]', 'code' . $rand);
     // БУДЕТ НОВЫЙ ID у инпута id="add_coupon_code"
        $this->promo_code = 'code' . $rand;
        $I->clickElement('li[id*="bonuses"]');
        $I->clickElement('a[id*="add_bonus"]');
        $I->selectOption('select[name*="[bonuses]"]', 'free_shipping');
        $I->waitForElement('div[style*="display: none;"]', 5);
        $I->clickElement('a[data-ca-dispatch*="dispatch[promotions.update]"]');
        $this->promo_id = $I->grabFromCurrentUrl('~promotion_id=(\d+)~');
        $I->seeInCurrentUrl('dispatch=promotions.update');
    }
    
    public function ConfirmAccountUser(AcceptanceTester $I, $scenario)
    {
        $I->wantTo('Confirm account affiliate, define a plan');
        $I->loadSessionSnapshot('login_admin');
        $this->current_page = $this->admin_index . '?dispatch=partners.manage';
        $I->amOnPage($this->current_page);
        $affiliate = $I->grabMultiple('tr[class*="cm-row-status-a"]');
        foreach ($affiliate as $value) {
            if (preg_match("/$this->email_user/", $value)){
                $this->user_id = preg_split("/ /", $value)[0];
                $this->affiliate_admin_url = $this->admin_index . '?dispatch=profiles.update&user_id=' . $this->user_id . '&user_type=P';
                break;
            }
    //Здесь добавим ID affiliate на страницу после регистрации
        }
        $I->amOnPage($this->affiliate_admin_url);
        $I->clickElement('li[id="affiliate_information"]');
        $I->selectOption('select[name*="update_data[approved]"]', 'A');
        $I->selectOption('select[id*="elm_affiliate_plan"]', $this->plan_name);
        $I->selectOption('select[name*="update_data[coupon_code]"]', $this->promo_code);
        $I->clickElement('a[data-ca-dispatch*="dispatch[profiles.update]"]');
    }

    public function CheckConfirmMenu(AcceptanceTester $I, $scenario)
    {
        $I->wantTo('Check confirmed affiliate menu');
        $I->loadSessionSnapshot('login_affiliate');
        $this->current_page = 'index.php?dispatch=affiliate_plans.list';
        $I->amOnPage($this->current_page);
        $I->SeeElementInDOM('div[class*="clearfix affiliate-plan-block"]');
    }

    public function CreateAffiliateOnReferalLink(AcceptanceTester $I, $scenario)
    {
        $I->wantTo('Creating a new affiliate by referral link');
        $this->referal_link = '/profiles-add/?aff_id='  .  $this->user_id;
        $rand = rand(1000, 9999);
        $I->amOnPage($this->referal_link);
        $I->selectOption('select[id*="user_type"]', 'P');
        $I->fillField('input[id*="email"]', 'referal' . $rand . '@example.com');
        $I->fillField('input[id*="password1"]', 'referal');
        $I->fillField('input[id*="password2"]', 'referal');
        $I->fillField('input[name*="user_data[firstname]"]', 'Referal' . $rand);
        $this->referal_name = 'Referal' . $rand;
        $I->fillField('input[name*="user_data[lastname]"]', 'Test');
        $I->fillField('input[name*="user_data[phone]"]', '+11111111111');
        $I->clickElement('button[name*="dispatch[profiles.update]"]');
        $I->waitForElement('h1[class*="ty-mainbox-title"]', 5);
        $I->seeInCurrentUrl('?dispatch=profiles.success_add');
        $I->amOnPage('/');
        $I->saveSessionSnapshot('login_referal_affilate');
    }

    public function CheckBalanceForNewAffiliate(AcceptanceTester $I, $scenario)
    {
        $I->wantTo('Check balance for new affiliate and creation tree affiliate');
        $I->loadSessionSnapshot('login_admin');
        $I->amOnPage($this->affiliate_admin_url);
        $I->clickElement('li[id="affiliate_information"]');
        $I->seeInSource('<span>6.00</span>');
        //БУДЕТ НОВЫЙ ID у span id="reward_balance"
        $I->clickElement('li[id*="affiliate_tree"]');
        $I->clickElement('span[class*="exicon-expand"]');
        $search_string = $this->referal_name . ' Test';
        $I->seeInPageSource($search_string);
        $this->referal_admin_url = $I->grabMultiple('td[class*="partners-tree-table-width"] a', 'href');
    }

    public function CheckUsePromocodeReferal(AcceptanceTester $I, $scenario)
    {
        $I->wantTo('Using the promo code on behalf of the referal');
        $I->loadSessionSnapshot('login_referal_affilate');
        $I->addProductToCart();
        $this->current_page = $this->customer_index . '?dispatch=checkout.checkout';
        $I->amOnPage($this->current_page);
        $I->fillField('input[id*="coupon_field"]', $this->promo_code);
        $I->pressKey('input[id*="coupon_field"]',WebDriverKeys::ENTER);
        $I->waitForElement('div[style*="display: none;"]', 10);
        $I->fillField('input[name*="user_data[s_address]"]', 'Ульяновск');
        $I->checkOption('input[name*= "accept_terms"]');
        $method_purchase = 'label[id=payments_' . $this->payment_id . ']';
        $I->click($method_purchase);
        $I->waitForElement('div[style*="display: none;"]', 10);
        $I->click('button[id="litecheckout_place_order"]');
        $I->waitForElement('div[class*="ty-checkout-complete"]', 10);
        $I->seeInCurrentUrl('?dispatch=checkout.complete');
        $this->order_id = $I->grabFromCurrentUrl('~.*order_id=(\d*)~');
    }

    public function ChangeTheStatusOfTheOrder(AcceptanceTester $I, $scenario)
    {
        $I->wantTo('Change the status of the order');
        $I->loadSessionSnapshot('login_admin');
        $this->current_page = $this->admin_index . '?dispatch=orders.details&order_id=' . $this->order_id;
        $I->amOnPage($this->current_page);
        $I->click('//div[contains(@class, "controls")]/div[1]/a');
        $I->click('//div[@class="control-group"]//a[contains(@onclick, "\'c\'")]');
        $I->waitForElement('div[style*="display: none;"]', 15);
        $I->click('//div[@class="actions__wrapper "]//a[@data-ca-dispatch]');
        
    }

    public function CheckBalanceForCode(AcceptanceTester $I, $scenario)
    {
        $I->wantTo('Check balance for use promo code');
        $I->loadSessionSnapshot('login_admin');
        $I->amOnPage($this->affiliate_admin_url);
        $I->clickElement('li[id="affiliate_information"]');
        $I->seeInSource('<span>19.00</span>');
        //БУДЕТ НОВЫЙ ID у span id="reward_balance"
    }

        public function RemoveTestData(AcceptanceTester $I, $scenario)
    {
        $I->wantTo('Delete all test data');
        $I->loadSessionSnapshot('login_admin');
        //Delete customer referal
        $this->current_page = $this->admin_index . '?dispatch=profiles.update&user_id=' . $this->customer_ref_id;
        $I->amOnPage($this->current_page);
        $I->click('//div[contains(@class, "btn-toolbar")]//a[@class="btn dropdown-toggle"]');
        $I->click('//a[contains(@href, "delete")]');
        $I->acceptPopup();
        //Delete customer
        $this->current_page = $this->admin_index . '?dispatch=profiles.update&user_id=' . $this->customer_id;
        $I->amOnPage($this->current_page);
        $I->click('//div[contains(@class, "btn-toolbar")]//a[@class="btn dropdown-toggle"]');
        $I->click('//a[contains(@href, "delete")]');
        $I->acceptPopup();
        //Delete promo action
        $this->current_page = $this->admin_index . '?dispatch=promotions.update&promotion_id=' . $this->promo_id;
        $I->amOnPage($this->current_page);
        $I->click('//div[contains(@class, "btn-toolbar")]//a[@class="btn dropdown-toggle"]');
        $I->click('//a[contains(@href, "delete")]');
        $I->acceptPopup();
        //Delete promo action
        $this->current_page = $this->admin_index . '?dispatch=affiliate_plans.manage';
        $I->amOnPage($this->current_page);
        $plan_path = '//input[@type="checkbox"][@value="' . $this->plan_id . '"]';
        $I->checkOption($plan_path);
        $I->click('//div[contains(@class, "btn-toolbar")]//a[@class="btn dropdown-toggle"]');
        $I->click('//a[contains(@data-ca-dispatch, "delete")]');
        $I->acceptPopup();
        //Delete affiliate
        $I->amOnPage($this->affiliate_admin_url);
        $I->click('//div[contains(@class, "btn-toolbar")]//a[@class="btn dropdown-toggle"]');
        try{
            $I->click('button[class="close cm-notification-close"]');
        }
        catch(Exception $ex){
            echo $ex->getMessage();
        }
        $I->click('//a[contains(@href, "delete")]');
        $I->acceptPopup();
        //Delete order
        $this->current_page = $this->admin_index . '?dispatch=orders.manage';
        $I->amOnPage($this->current_page);
        $order_path = '//input[@type="checkbox"][@value="' . $this->order_id . '"]';
        $I->checkOption($order_path);
        $I->click('//div[contains(@class, "btn-toolbar")]//a[@class="btn dropdown-toggle"]');
        $I->click('//a[contains(@data-ca-dispatch, "delete")]');
        $I->acceptPopup();
        //Delete referal Affiliate
        $I->amOnUrl(end($this->referal_admin_url));
        $I->click('//div[contains(@class, "btn-toolbar")]//a[@class="btn dropdown-toggle"]');
        $I->click('//a[contains(@href, "delete")]');
        $I->acceptPopup();
    }
}