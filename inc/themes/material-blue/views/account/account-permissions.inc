<?php
/** @var \SP\Account\AccountAcl $AccountAcl */
/** @var \SP\DataModel\AccountExtData $accountData */
?>

<table class="data round extra-info">
    <?php if ($AccountAcl->isShowPermission()): ?>
        <tr>
            <td class="descField"><?php echo _('Permisos'); ?></td>
            <td class="valField">
                <div class="account-permissions">
                    <fieldset class="round5">
                        <legend><?php echo _('Usuarios'); ?></legend>
                        <select id="otherUsers" name="otherUsers[]" multiple="multiple">
                            <option value=""><?php echo _('Seleccionar Usuarios'); ?></option>
                            <?php if ($gotData === true): ?>
                                <?php foreach ($accountOtherUsers as $otherUser): ?>
                                    <?php /** @var $otherUser \SP\DataModel\UserData */ ?>
                                    <option
                                        value="<?php echo $otherUser->getUserId(); ?>"
                                        selected><?php echo $otherUser->getUserLogin(); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <br>
                        <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="userEditEnabled">
                            <input name="userEditEnabled" type="checkbox" id="userEditEnabled"
                                   class="mdl-switch__input" <?php echo ($gotData && $accountData->getAccountOtherUserEdit()) ? 'checked' : ''; ?>/>
                            <span class="mdl-switch__label"><?php echo _('Hablitar edición'); ?></span>
                        </label>
                    </fieldset>
                </div>
                <div class="account-permissions">
                    <fieldset class="round5">
                        <legend><?php echo _('Grupos'); ?></legend>
                        <select id="otherGroups" name="otherGroups[]" multiple="multiple">
                            <option value=""><?php echo _('Seleccionar Grupos'); ?></option>
                            <?php if ($gotData === true): ?>
                                <?php foreach ($accountOtherGroups as $otherGroup): ?>
                                    <?php /** @var $otherGroup \SP\DataModel\GroupData */ ?>
                                    <option
                                        value="<?php echo $otherGroup->getUsergroupId(); ?>"
                                        selected><?php echo $otherGroup->getUsergroupName(); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <br>
                        <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="groupEditEnabled">
                            <input name="groupEditEnabled" type="checkbox" id="groupEditEnabled"
                                   class="mdl-switch__input" <?php echo ($gotData && $accountData->getAccountOtherGroupEdit()) ? 'checked' : ''; ?>/>
                            <span class="mdl-switch__label"><?php echo _('Hablitar edición'); ?></span>
                        </label

                    </fieldset>
                </div>
            </td>
        </tr>
    <?php endif; ?>
    <?php if ($userIsAdminApp || $userIsAdminAcc): ?>
        <tr>
            <td class="descField"><?php echo _('Grupo Principal'); ?></td>
            <td class="valField">
                <select id="selMainGroupId" name="mainGroupId" class="select-box sel-chosen-usergroup" required>
                    <option value=""><?php echo _('Seleccionar Grupo'); ?></option>
                    <?php foreach ($otherGroups as $group): ?>
                        <option
                            value="<?php echo $group->id; ?>" <?php echo ($gotData && $group->id == $accountData->getAccountUserGroupId()) ? 'selected' : ''; ?>><?php echo $group->name; ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
    <?php endif; ?>
    <?php if ($allowPrivate === 1): ?>
        <tr>
            <td class="descField"><?php echo _('Cuenta Privada'); ?></td>
            <td class="valField">
                <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="privateEnabled">
                    <input name="privateEnabled" type="checkbox" id="privateEnabled"
                           class="mdl-switch__input" <?php echo ($gotData && $accountData->getAccountIsPrivate()) ? 'checked' : ''; ?>/>
                    <span class="mdl-switch__label"><?php echo _('Privada'); ?></span>
                </label>
            </td>
        </tr>
    <?php endif; ?>
</table>
