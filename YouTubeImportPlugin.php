<?php
/**
 * YouTube Import plugin
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */


/**
 * YouTube Import plugin class
 */
class YouTubeImportPlugin extends Omeka_Plugin_AbstractPlugin
{
  /**
   * @var array Hooks for the plugin.
   */
    protected $_hooks = array('define_acl','install','admin_head','after_save_item');

  /**
   * @var array Filters for the plugin.
   */
  protected $_filters = array('admin_navigation_main');

  public function hookAfterSaveItem($args){

      $item = $args['record'];                                          
      $element = $this->_db->getTable("Element")->findByElementSetNameAndElementName('Item Type Metadata',"Player");
      if($players = $this->_db->getTable("ElementText")->findBy(array('record_id'=>$item->id,'element_id'=>$element->id))) {
          if(!is_array($players))
              $players = array($players);
          foreach ($players as $player) {
              $player->html = 1;
              $player->save();
          }
      }
  }

  /**
   *When the plugin installs, create a new metadata element
   *called Player associated with Moving Pictures
   *
   *@return void
   */
  public function hookInstall(){

    if(element_exists(ElementSet::ITEM_TYPE_NAME,'Player'))
      return;

    $db = get_db();
    $table = $db->getTable('ItemType');
    $mpType = $table->findByName('Moving Image');
    $mpType->addElements(array(
			       array(
				     'name'=>'Player',
				     'description'=>'html for embedded player to stream video content'
				     )
			       ));
    $mpType->save();
  
  }

  /**
   *When the plugin loads on the admin side, 
   *queue the css file
   *
   *@return void
   */
  public function hookAdminHead(){
      if(  $playerElement = $this->_db->getTable("Element")->findByElementSetNameAndElementName("Item Type Metadata","Player")) {
          queue_js_string("var playerElementId = ".$playerElement->id.';');
          queue_js_file('YoutubeImport');
      }
      queue_css_file('YoutubeImport');
  }

  /**
   * Define the plugin's access control list.
   *
   *@param array $args Arguments passed from Zend
   *@return void
   */
  public function hookDefineAcl($args)
  {
    $args['acl']->addResource('YoutubeImport_Index');
  }

   
  /**
   * Add the Youtube Import link to the admin main navigation.
   * 
   * @param array $nav Navigation array.
   * @return array $nav Filtered navigation array.
   */
  public function filterAdminNavigationMain($nav)
  {
    $nav[] = array(
		   'label' => __('YouTube Import'),
		   'uri' => url('you-tube-import'),
		   'resource' => 'YoutubeImport_Index',
		   'privilege' => 'index'
		   );
    return $nav;
  }
    
}
