<?php die(); ?>

<div class="title">
    <h2>{LANG_PASSWORD} {LANG_LOST}</h2>
</div>

{LOGIN_MSG_WRAPPER}

<div id="loginLost_container" class="card card-body">
    <p>{LANG_LOST_MSG}</p>

    <form action="" method="post">
        {CSRF_TOKEN}
            <label class="form-label">{LANG_LOST_INFO}</label>
            <input class="form-control" type="text" name="email" />
        </div>
        <div class="form-actions">
            <input class="btn btn-secondary" type="button" onclick="window.location='?com={USER_COM_ID}';" value="{LANG_CANCEL}" />
            <input class="btn btn-primary" type="submit" name="submit" value="{LANG_SEND}" />
        </div>
    </form>
</div>
