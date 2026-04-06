<?php die(); ?>

<div class="title">
    <h2>{COM_NAME}</h2>
</div>

<div id="subleague-table" class="card card-body">
    <form action="" method="post" enctype="multipart/form-data">
        <div class="table-responsive">
            <table class="list" cellpadding="0" cellspacing="0">
                <tr>
                    <th>#</th>
                    <th>{LANG_POSITION}</th>
                    <th>{LANG_PARTICIPANT}</th>
                    <th>{LANG_POINTS}</th>
                </tr>

                {CONTENT}
            </table>
        </div>
    </form>
</div>