<?php die(); ?>

<div class="title">
    <h2>{LANG_PASSWORD} {LANG_LOST}</h2>
</div>

{LANG_LOST_MSG}

<div id="loginLost_container" class="card">
    <div class="card-body">
        <form action="" method="post">
            <div class="form-group">
                <label class="form-label">{LANG_LOST_INFO}</label>
                <input class="form-control" type="text" name="email" />
            </div>
            <div class="form-actions">
                <input class="btn btn-primary" type="submit" name="submit" value="{LANG_SEND}" />
            </div>
        </form>
    </div>
</div>
