{if $PROFILE_ERROR neq ""}
<div class="alert alert-error">{$PROFILE_ERROR}</div>
{/if}

{if $smarty.get.a eq "ok"}
<div class="alert alert-success">Аккаунт успешно зарегистрирован!<br/>
    Вы сможете войти в систему после активации аккаунта администратором сайта. Вам будет направлено уведомление.</div>
{/if}

{if $smarty.get.a eq "r"}

<form action="" class="form-horizontal" method="post">
    <input type="hidden" name="RegIt" value="1">
    <div class="control-group">
        <label class="control-label" for="inputName">Ваше имя</label>

        <div class="controls">
            <input type="text" id="inputName" name="USER[NAME]" placeholder="Ваше имя">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="inputEmail">E-Mail</label>

        <div class="controls">
            <input type="text" id="inputEmail" name="USER[EMAIL]" placeholder="E-Mail">
        </div>
    </div><div class="control-group">
        <label class="control-label" for="inputPass1">Пароль</label>

        <div class="controls">
            <input type="password" id="inputPass1" name="USER[PASS]" placeholder="*****">
        </div>
    </div><div class="control-group">
        <label class="control-label" for="inputPass2">Повторите пароль</label>

        <div class="controls">
            <input type="password" id="inputPass2" name="USER[REPASS]" placeholder="*****">
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
            <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
        </div>
    </div>
</form>

    {else}

<form action="" class="form-horizontal" method="post">
    <input type="hidden" name="UserLogon" value="1">

    <div class="control-group">
        <label class="control-label" for="inputEmail">E-Mail</label>

        <div class="controls">
            <input type="text" id="inputEmail" name="UserLogin" value="{$smarty.post.UserLogin}" placeholder="E-Mail">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="inputPassword">Пароль</label>

        <div class="controls">
            <input type="password" id="inputPassword" name="UserPassword" placeholder="Пароль">
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
            <label><a href="?a=r">Регистрация в системе</a></label>
            <button type="submit" class="btn">Войти</button>
        </div>
    </div>
</form>

{/if}