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

class MOUSE_CTRL_Messages extends MAILBOX_CTRL_Messages
{
    public function index($params)
    {
        parent::index($params);

        $this->setTemplate(OW::getPluginManager()->getPlugin('mouse')->getCtrlViewDir() . 'messages_index.html');
    }

    public function chatConversation( $params )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        if ( !OW::getUser()->isAuthorized('mailbox', 'send_chat_message') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', 'send_chat_message');
            throw new AuthorizationException($status['msg']);
        }
        
        $opponentId = isset($params['userId']) ? $params['userId'] : null;

        if( !BOL_UserService::getInstance()->findUserById($opponentId) )
        {
            throw new Redirect404Exception(); 
        }

        $userId = OW::getUser()->getId();

        $convId = MAILBOX_BOL_ConversationDao::getInstance()->findChatConversationIdWithUserById($userId, $opponentId);

        if( empty($convId) )
        {
            $convDto = MAILBOX_BOL_ConversationService::getInstance()->createChatConversation($userId, $opponentId);
            $convId = (int) $convDto->id;
        }

        // $params['convId'] = $convId;
        $this->index( $params );
    
        OW::getDocument()->addOnloadScript(UTIL_JsGenerator::composeJsString('$(document).ready(function(){
            OW.trigger("mailbox.conversation_item_selected", {
                convId: {$convId},
                opponentId: {$opponentId}
            });
            
            if(window.innerWidth <= 768){
				enterMailboxChat();
			}
        });', [
            'opponentId' => $opponentId,
            'convId' => $convId,
        ]));
    }
}