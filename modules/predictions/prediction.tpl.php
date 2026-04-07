<?php die(); ?>

<div class="title">
    <h2>{COM_NAME}</h2>
</div>

{PREDICTION_MSG}{ERROR_MSG}
{SUBMISSION_MSG}
{PAYMENT_MSG}

{PREDICTION_EDIT}
{USER_CONTENT}

<div id="prediction" class="card card-body">
    <form action="" method="post" enctype="multipart/form-data">
        {GAME_CONTENT}
        {ROUND_CONTENT}
        {QUESTION_CONTENT}
        <div class="mt-3">
            {PREDICTION_BUTTONS}
        </div>
    </form>
</div>