<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class mcb extends front_base {

    public function __construct() {
        parent::__construct();
    }
    
	public function dispute_intro()
	{
		$tpl = 'mcb/dispute_intro.html';
		$skin = $this->skin;

		$this->template_path = $tpl;
		$this->template->assign(array("template_path"=>$this->template_path));
	
		$this->print_layout($skin.'/'.$tpl);
	}
	
}