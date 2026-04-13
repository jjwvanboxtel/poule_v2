<?php die(); ?>

<div class="title">
    <h2>{COM_NAME}</h2>
</div>
{TABLE_MSG}

<div id="table-standings" class="card card-body">
    <form action="" method="post" enctype="multipart/form-data">
        <div class="table-responsive">
            <table class="list" cellpadding="0" cellspacing="0">
                <tr>
                    <th colspan="2">{LANG_POSITION}</th>
                    <th>{LANG_PARTICIPANT}</th>
                    <th>{LANG_POINTS}</th>
                </tr>

                {CONTENT}
            </table>
        </div>
        <div class="mt-3">
            {TABLE_BUTTONS}
        </div>
    </form>
</div>