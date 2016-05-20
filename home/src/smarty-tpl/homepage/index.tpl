{extends file='_common/layout-homepage.tpl'}
{block name=script}
{literal}
$(function() {
    $('#issue_submit').click(function() {
        var issue_contact = $('#issue_contact').val();
        var issue_title = $('#issue_title').val();
        var issue_content = $('#issue_content').val();
        if (issue_contact.length < 1 || issue_title.length < 1) {
            alert('联系信息和标题为必填项');
            return ;
        }
        $.post('/homepage/issue_submit', {
            issue_contact: issue_contact,
            issue_title: issue_title,
            issue_content: issue_content
        }, function(d) {
            $('#issue_contact').val('');
            $('#issue_title').val('');
            $('#issue_content').val('');
            if (d.code == 0) {
                alert('提交成功');
            } else {
                alert(d.data.msg);
            }
        }, 'json');
    });
});
{/literal}
{/block}
{block name=style}
table {
    width: 100%;
    border: 0px solid #FFFFFF;
    background-color: #F8F8F8;
}
table td, table th {
    border: 0px solid #F0F0F0;
}
.table-left {
    width: 30%;
    vertical-align: text-top;
}
.table-left input[type=text] {
    width: 140px;
    border: 0px solid black;
    border-bottom: 1px solid #E0E0E0;
    background-color: #F8F8F8;
}
.table-right input[type=text] {
    width: 220px;
    border: 0px solid black;
    border-bottom: 1px solid #E0E0E0;
    background-color: #F8F8F8;
}
.table-right textarea {
    width: 100%;
    border: 1px solid #E0E0E0;
    background-color: #F8F8F8;
}
{/block}
{block name=body}
{$body_content}
<form method="POST" action="/homepage/issue_submit">
    <table border="1" cellpading="0" cellspacing="0" valign="top">
        <tr>
            <th colspan="2">提交您的问题</th>
        </tr>
        <tr>
            <td class="table-left"><font color="red">*</font>联系信息: <input type="text" id="issue_contact" /></td>
            <td class="table-right">
                <font color="red">*</font>标题: <input type="text" id="issue_title"/><br />
                正文: <textarea id="issue_content" rows="8"></textarea>
            </td>
        </tr>
        <tr>
            <th colspan="2"><input type="button" id="issue_submit" value="提交" /></th>
        </tr>
    </table>
</form>
{/block}
