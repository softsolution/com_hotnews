{* ================================================================================ *}
{* =========== Форма редактирования важной новости  =============================== *}
{* ================================================================================ *}

{if $do=='edit'}
    <div class="wrap_quest_button">
        <div id="questbutton">
            <a class="button-1" title="Удалить вопрос" href="/otvety/delete{$item.id}.html"  onclick="return confirm('Вы действительно хотите удалить вопрос?')"><span class="txt">Удалить вопрос</span><span class="bg-button"></span></a>
        </div>
    </div>
{/if}

<h1 class="con_heading">{if $do=='edit'}{$LANG.EDIT_QUEST}{else}{$LANG.SET_QUESTION}{/if}</h1>
<div class=clear></div>

{if !$user_id}<div style="margin-bottom:10px">{$LANG.CONTACTS_TEXT}<br />Ваш вопрос будет опубликован только после проверки модератором.</div>{/if}

{if $error}<p style="color:red">{$error}</p>{/if}

<form action="" method="POST" name="questform" id="questform">
    <table width="100%" border="0" cellpadding="6" cellspacing="0" class="addquesttbl">
        <tr>
            <td width="160"><strong>Заголовок вопроса: </strong></td>
            <td><input name="title" class="text-input" type="text" id="title" style="width:400px" value="{$item.title|escape:'html'}"/></td>
        </tr>

        <tr>
            <td><strong>{$LANG.CAT_QUESTIONS}:</strong></td>
            <td>
                <select name="category_id" id="category_id" style="width:422px">
                    {$catslist}
                </select>
            </td>
        </tr>
        <tr>
            <td valign="top">
                <strong>{$LANG.TAGS}:</strong>
            </td>
            <td>
                <input name="tags" class="text-input" type="text" id="tags" style="width:400px" value="{$item.tags|escape:'html'}"/><br />
                <span class="hinttext" style="font-size:11px">{$LANG.KEYWORDS}</span>
                <script type="text/javascript">
                    {$autocomplete_js}
                </script>
            </td>
        </tr>
    <tr>
        {if !$user_id}<tr><td><strong>Имя:</strong><td><input type=text name="anonimname" value="{$anonimname}">{/if}

        <tr><td colspan="2"><p><strong>Ваш вопрос:</strong></p>
            <div class="usr_msg_bbcodebox">{$bb_toolbar}</div>
            {$smilies}
            {$autogrow}
            <div><textarea class="ajax_autogrowarea" name="question" id="question" style="width:100%" >{$item.question|escape:'html'}</textarea></div>
            </td>
        </tr>
</table>

    {if !$user_id}
        <p style="margin-bottom:10px">
            {php}echo cmsPage::getCaptcha();{/php}
        </p>
    {/if}

    <div>
        <a class="button-1" onClick="sendQuestform()"><span class="txt">{if $do=='edit'}{$LANG.SAVE}{else}{$LANG.ASK_QUES}{/if}</span><span class="bg-button"></span></a>
        <a class="button-1" onClick="window.history.go(-1)"><span class="txt">{$LANG.CANCEL}</span><span class="bg-button"></span></a>
    </div>
</form>