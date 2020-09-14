{if $smarty.session.module == 'erip'}
{if $smarty.session.status == 'ok'}
<h3>Ваш номер заказа: {$smarty.session.order_id}</h3>
<table style="width: 100%;text-align: left;">
    <tbody>
        <tr>
            <td valign="top" style="text-align:left;">
                Вам необходимо произвести платеж в любой системе, позволяющей проводить оплату через ЕРИП (пункты банковского обслуживания, банкоматы, платежные терминалы, системы интернет-банкинга, клиент-банкинга и т.п.).
                <br />
                <br /> 1. Для этого в перечне услуг ЕРИП перейдите в раздел: <br />
                <b>{$smarty.session.erip_path}</b><br />
                <br /> 2. В поле "Номер заказа" введите <b>{$smarty.session.order_id}</b> и нажмите "Продолжить"<br />
                <br />3. Укажите сумму для оплаты <b>{$smarty.session.amount}</b><br />
                <br />4. Совершить платеж.<br />
            </td>
            {if $smarty.session.showQrCode}
            <td style="text-align: center;padding: 0px 0px 0 0;vertical-align: middle">
            <img src="data:image/jpeg;base64,{$smarty.session.qr_code} "width="200" height="200"/></p>
                <p><b>{$smarty.session.qr_description}</b></p>
            </td>
        {/if}
        </tr>
    </tbody>
</table>
{else}
<h3>Ваш номер заказа: {$smarty.session.order_id}</h3>
<p>При выполнении запроса произошла непредвиденная ошибка. Пожалуйста, повторите запрос позже или обратитесь в службу технической поддержки магазина</p>
{/if}
{/if}