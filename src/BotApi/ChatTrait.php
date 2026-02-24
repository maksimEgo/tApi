<?php

namespace TelegramBot\Api\BotApi;

use TelegramBot\Api\Types\ArrayOfChatMemberEntity;
use TelegramBot\Api\Types\ChatFullInfo;
use TelegramBot\Api\Types\ChatInviteLink;
use TelegramBot\Api\Types\ChatMember;
use TelegramBot\Api\Types\UserChatBoosts;

trait ChatTrait
{
    public function kickChatMember($chatId, $userId, $untilDate = null)
    {
        @trigger_error(sprintf('Method "%s::%s" is deprecated. Use "banChatMember"', __CLASS__, __METHOD__), \E_USER_DEPRECATED);

        return $this->call('kickChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'until_date' => $untilDate
        ]);
    }

    public function banChatMember($chatId, $userId, $untilDate = null, $revokeMessages = null)
    {
        return $this->call('banChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'until_date' => $untilDate,
            'revoke_messages' => $revokeMessages,
        ]);
    }

    public function unbanChatMember($chatId, $userId)
    {
        return $this->call('unbanChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    public function restrictChatMember(
        $chatId,
        $userId,
        $untilDate = null,
        $canSendMessages = false,
        $canSendMediaMessages = false,
        $canSendOtherMessages = false,
        $canAddWebPagePreviews = false
    ) {
        return $this->call('restrictChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'until_date' => $untilDate,
            'can_send_messages' => $canSendMessages,
            'can_send_media_messages' => $canSendMediaMessages,
            'can_send_other_messages' => $canSendOtherMessages,
            'can_add_web_page_previews' => $canAddWebPagePreviews
        ]);
    }

    public function promoteChatMember(
        $chatId,
        $userId,
        $canChangeInfo = true,
        $canPostMessages = true,
        $canEditMessages = true,
        $canDeleteMessages = true,
        $canInviteUsers = true,
        $canRestrictMembers = true,
        $canPinMessages = true,
        $canPromoteMembers = true,
        $canManageTopics = true,
        $isAnonymous = false
    ) {
        return $this->call('promoteChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'is_anonymous' => $isAnonymous,
            'can_change_info' => $canChangeInfo,
            'can_post_messages' => $canPostMessages,
            'can_edit_messages' => $canEditMessages,
            'can_delete_messages' => $canDeleteMessages,
            'can_invite_users' => $canInviteUsers,
            'can_restrict_members' => $canRestrictMembers,
            'can_pin_messages' => $canPinMessages,
            'can_promote_members' => $canPromoteMembers,
            'can_manage_topics' => $canManageTopics
        ]);
    }

    public function exportChatInviteLink($chatId)
    {
        return $this->call('exportChatInviteLink', [
            'chat_id' => $chatId
        ]);
    }

    public function createChatInviteLink($chatId, $name = null, $expireDate = null, $memberLimit = null, $createsJoinRequest = null): ChatInviteLink
    {
        return ChatInviteLink::fromResponse($this->call('createChatInviteLink', [
            'chat_id' => $chatId,
            'name' => $name,
            'expire_date' => $expireDate,
            'member_limit' => $memberLimit,
            'creates_join_request' => $createsJoinRequest,
        ]));
    }

    public function editChatInviteLink($chatId, $inviteLink, $name = null, $expireDate = null, $memberLimit = null, $createsJoinRequest = null): ChatInviteLink
    {
        return ChatInviteLink::fromResponse($this->call('editChatInviteLink', [
            'chat_id' => $chatId,
            'invite_link' => $inviteLink,
            'name' => $name,
            'expire_date' => $expireDate,
            'member_limit' => $memberLimit,
            'creates_join_request' => $createsJoinRequest,
        ]));
    }

    public function revokeChatInviteLink($chatId, $inviteLink): ChatInviteLink
    {
        return ChatInviteLink::fromResponse($this->call('revokeChatInviteLink', [
            'chat_id' => $chatId,
            'invite_link' => $inviteLink,
        ]));
    }

    public function approveChatJoinRequest($chatId, $userId)
    {
        return $this->call('approveChatJoinRequest', [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    public function declineChatJoinRequest($chatId, $userId)
    {
        return $this->call('declineChatJoinRequest', [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    public function setChatPhoto($chatId, $photo)
    {
        return $this->call('setChatPhoto', [
            'chat_id' => $chatId,
            'photo' => $photo
        ]);
    }

    public function deleteChatPhoto($chatId)
    {
        return $this->call('deleteChatPhoto', [
            'chat_id' => $chatId
        ]);
    }

    public function setChatTitle($chatId, $title)
    {
        return $this->call('setChatTitle', [
            'chat_id' => $chatId,
            'title' => $title
        ]);
    }

    public function setChatDescription($chatId, $description = null)
    {
        return $this->call('setChatDescription', [
            'chat_id' => $chatId,
            'title' => $description
        ]);
    }

    public function pinChatMessage($chatId, $messageId, $disableNotification = false)
    {
        return $this->call('pinChatMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'disable_notification' => $disableNotification
        ]);
    }

    public function unpinChatMessage($chatId, $messageId = null)
    {
        return $this->call('unpinChatMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    public function getChat($chatId): ChatFullInfo
    {
        return ChatFullInfo::fromResponse($this->call('getChat', [
            'chat_id' => $chatId
        ]));
    }

    public function getChatMember($chatId, $userId)
    {
        return ChatMember::fromResponse($this->call('getChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId
        ]));
    }

    public function leaveChat($chatId)
    {
        return $this->call('leaveChat', [
            'chat_id' => $chatId
        ]);
    }

    public function getChatMembersCount($chatId)
    {
        @trigger_error(sprintf('Method "%s::%s" is deprecated. Use "getChatMemberCount"', __CLASS__, __METHOD__), \E_USER_DEPRECATED);

        return $this->call('getChatMembersCount', [
            'chat_id' => $chatId
        ]);
    }

    public function getChatMemberCount($chatId)
    {
        return $this->call('getChatMemberCount', [
            'chat_id' => $chatId
        ]);
    }

    public function getChatAdministrators($chatId): array
    {
        return ArrayOfChatMemberEntity::fromResponse(
            $this->call(
                'getChatAdministrators',
                [
                    'chat_id' => $chatId
                ]
            )
        );
    }

    public function getUserChatBoosts($chatId, $userId): UserChatBoosts
    {
        return UserChatBoosts::fromResponse($this->call('getUserChatBoosts', [
            'chat_id' => $chatId,
            'user_id' => $userId
        ]));
    }
}
