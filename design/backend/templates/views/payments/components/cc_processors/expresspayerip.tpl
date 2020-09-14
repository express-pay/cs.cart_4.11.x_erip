<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="mode">
            <option value="test" {if $processor_params.mode == "test"}selected="selected"{/if}>{__("test")}</option>
            <option value="live" {if $processor_params.mode == "live"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
  <label class="control-label" for="token">Токен</label>
  {include file="common/tooltip.tpl" tooltip="Можно узнать в личном кабинете сервиса \"Экспресс Платежи\" в настройках услуги."}
  <div class="controls">
    <input type="text" name="payment_data[processor_params][token]" id="token" value="{$processor_params.token}" class="input-text" />
  </div>
</div>

<div class="control-group">
  <label class="control-label" for="serviceId">Номер услуги</label>
  {include file="common/tooltip.tpl" tooltip="Можно узнать в личном кабинете сервиса \"Экспресс Платежи\" в настройках услуги."}
  <div class="controls">
    <input type="text" name="payment_data[processor_params][serviceId]" id="serviceId" value="{$processor_params.serviceId}" class="input-text" />
  </div>
</div>

<div class="control-group">
<label class="control-label" for="useSignature">Использовать цифровую подпись для выставления счетов</label>
{include file="common/tooltip.tpl" tooltip="Значение должно совпадать со значением, установленным в личном кабинете сервиса \"Экспресс Платежи\"."}
<div class="controls"><input type="hidden" name="payment_data[processor_params][useSignature]" value="N">
    <input type="checkbox" name="payment_data[processor_params][useSignature]" id="useSignature" value="Y" {if $processor_params.useSignature == "Y"}checked="checked"{/if}></div>
</div>

<div class="control-group">
  <label class="control-label" for="secretWord">Секретное слово для подписи счетов</label>
  {include file="common/tooltip.tpl" tooltip="Задается в личном кабинете, секретное слово должно совпадать с секретным словом, установленным в личном кабинете сервиса \"Экспресс Платежи\"."}
  <div class="controls">
    <input type="text" name="payment_data[processor_params][secretWord]" id="secretWord" value="{$processor_params.secretWord}" class="input-text" />
  </div>
</div>

{assign var="r_url" value="payment_notification.notify?payment=expresspayerip"|fn_url:'C':'https'}
<div class="control-group">
  <label class="control-label" for="notifUrl">Адрес для уведомлений</label>
  <div class="controls">
    <input type="text" name="payment_data[processor_params][notifUrl]" id="notifUrl" value="{$r_url}" class="input-text" />
  </div>
</div>

<div class="control-group">
<label class="control-label" for="useSignatureForNotif">Использовать цифровую подпись для уведомлений</label>
{include file="common/tooltip.tpl" tooltip="Значение должно совпадать со значением, установленным в личном кабинете сервиса \"Экспресс Платежи\"."}
<div class="controls"><input type="hidden" name="payment_data[processor_params][useSignatureForNotif]" value="N">
    <input type="checkbox" name="payment_data[processor_params][useSignatureForNotif]" id="useSignatureForNotif" value="Y" {if $processor_params.useSignatureForNotif == "Y"}checked="checked"{/if}></div>
</div>

<div class="control-group">
  <label class="control-label" for="secretWordForNotif">Секретное слово для уведомлений</label>
  {include file="common/tooltip.tpl" tooltip="Задается в личном кабинете, секретное слово должно совпадать с секретным словом, установленным в личном кабинете сервиса \"Экспресс Платежи\"."}
  <div class="controls">
    <input type="text" name="payment_data[processor_params][secretWordForNotif]" id="secretWordForNotif" value="{$processor_params.secretWordForNotif}" class="input-text" />
  </div>
</div>

<div class="control-group">
  <label class="control-label" for="pathToErip">Путь по ветке ЕРИП</label>
  <div class="controls">
    <input type="text" name="payment_data[processor_params][pathToErip]" id="pathToErip" value="{$processor_params.pathToErip}" class="input-text" />
  </div>
</div>

<div class="control-group">
<label class="control-label" for="showQrCode">Показывать Qr-код</label>
<div class="controls"><input type="hidden" name="payment_data[processor_params][showQrCode]" value="N">
    <input type="checkbox" name="payment_data[processor_params][showQrCode]" id="showQrCode" value="Y" {if $processor_params.showQrCode == "Y"}checked="checked"{/if}></div>
</div>

<div class="control-group">
<label class="control-label" for="isNameEdit">Разрешено изменять ФИО</label>
<div class="controls"><input type="hidden" name="payment_data[processor_params][isNameEdit]" value="N">
    <input type="checkbox" name="payment_data[processor_params][isNameEdit]" id="isNameEdit" value="Y" {if $processor_params.isNameEdit == "Y"}checked="checked"{/if}></div>
</div>

<div class="control-group">
<label class="control-label" for="isAmountEdit">Разрешено изменять сумму</label>
<div class="controls"><input type="hidden" name="payment_data[processor_params][isAmountEdit]" value="N">
    <input type="checkbox" name="payment_data[processor_params][isAmountEdit]" id="isAmountEdit" value="Y" {if $processor_params.isAmountEdit == "Y"}checked="checked"{/if}></div>
</div>

<div class="control-group">
<label class="control-label" for="isAddressEdit">Разрешено изменять адрес</label>
<div class="controls"><input type="hidden" name="payment_data[processor_params][isAddressEdit]" value="N">
    <input type="checkbox" name="payment_data[processor_params][isAddressEdit]" id="isAddressEdit" value="Y" {if $processor_params.isAddressEdit == "Y"}checked="checked"{/if}></div>
</div>

