<?php

/**
 * This software is intended for use with Skadate Software https://mouse.com/ and is a proprietary licensed product. 
 * For more information see License.txt in the plugin folder.

 * ---
 * Copyright (c) 2023, Peatech LLC
 * All rights reserved.
 * dev@peatechllc.com.

 * Redistribution and use in source and binary forms, with or without modification, are not permitted provided.

 * This plugin should be bought from the developer. For details contact dev@peatechllc.com.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class MOUSE_CMP_Hotlist extends OW_Component
{
    public function __construct( $count = 9 )
    {
        parent::__construct();

        if( !OW::getPluginManager()->isPluginActive('hotlist') )
        {
            $this->setVisible(false);
            return;
        }

        $service = HOTLIST_BOL_Service::getInstance();

        $authMsg = '';
        $authorized = OW::getUser()->isAuthorized('hotlist', 'add_to_list');

        if (!$authorized)
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('hotlist', 'add_to_list');
            $authMsg = json_encode($status['msg']);
       
        }

        $this->assign('authorized', $authorized);
        $this->assign('authMsg', $authMsg);

        $userId = OW::getUser()->getId();
        $userIsHot = false;
        $userList = $service->getHotList();

        $userIds = array();

        foreach ($userList as $item)
        {
            $userIds[] = $item->userId;
        }

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds);
        $event = new OW_Event('bookmarks.is_mark', array(), $avatars);
        OW::getEventManager()->trigger($event);

        if ( $event->getData() )
        {
            $avatars = $event->getData();
        }

        // check if current user is hot
        if( isset($avatars[$userId]) )
        {
            $userIsHot = true;
            $userInfo = $avatars[$userId];

            unset($avatars[$userId]);
        }
        else
        {
            $userAvatars = BOL_AvatarService::getInstance()->getDataForUserAvatars([$userId]);
            $userInfo = $userAvatars[$userId];
        }

        $this->assign('userList', $avatars);
        $this->assign('userIsHot', $userIsHot);
        $this->assign('userInfo', $userInfo);

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('mouse')->getStaticCssUrl() . 'owl.carousel.min.css');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('mouse')->getStaticCssUrl() . 'owl.theme.default.min.css');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('mouse')->getStaticJsUrl() . 'owl.carousel.min.js');

        OW::getDocument()->addOnloadScript('$(document).ready(function(){
            $(".ow_hotlist_carousel").owlCarousel({
                nav:true,
                loop:true,
                margin:10,
                responsiveClass:true,
                responsive:{
                    0:{
                        items:4,
                        nav:false
                    },
                    600:{
                        items:5,
                        nav:false
                    },
                    1000:{
                        items:9,
                    }
                }
            });
          });');
    }
    
  /*   public function render()
    {
        $hotlist = new HOTLIST_CMP_Index();
        $hotlist->setTemplate(OW::getPluginManager()->getPlugin('mouse')->getCmpViewDir() . 'hotlist.html');
        
        return $hotlist->render();
    } */
}