{* *****************************************************************************************
      показываем клиенту что его оплата фискализирована
      @todo: сделайте свой вариант вывода на основе примера
   ***************************************************************************************** *}
{include file='./kkmserver/ofd/default.tpl'}

{* *****************************************************************************************
                      Для админа
   ***************************************************************************************** *}
{if $smarty.session.admin == 'admin'}
  {include file='./kkmserver/admin_css_default.tpl'}
  {include file='./kkmserver/admin.tpl'}
{/if}