<?php die(); ?>

<div class="title">
    <h2>{COM_NAME}</h2>
</div>
<div>{PREDICTION_MSG}{ERROR_MSG}{SUBMISSION_MSG}</div>

{PREDICTION_EDIT}
{USER_CONTENT}

<div id="prediction" class="card card-body">
    <form action="" method="post" enctype="multipart/form-data">
        {GAME_CONTENT}
        {ROUND_CONTENT}
        {QUESTION_CONTENT}
        {PREDICTION_BUTTONS}
    </form>
</div>