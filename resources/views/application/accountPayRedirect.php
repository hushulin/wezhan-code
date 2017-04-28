<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>正在跳转</title>
        <meta content="always" name="referrer">
    </head>
    <body>

        <form name="redirect" accept-charset='utf-8' method="post" action="<?php echo $requestURL; ?>">
            <?php foreach ($parameters as $key => $value) { ?>
            <input type='hidden' name='<?php echo $key; ?>' value='<?php echo $value; ?>'/>
            <?php } ?>
            <input type='hidden' name='sign' value='<?php echo $sign; ?>'/>
        </form>

        <script>
            document.forms['redirect'].submit();
        </script>

    </body>
</html>
