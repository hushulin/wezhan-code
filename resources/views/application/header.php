<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="wap-font-scale" content="no">
    <meta name="format-detection" content="telephone=no">
    <title><?php echo isset($title) ? $title : env('APP_TITLE'); ?></title>
    <link rel="stylesheet" href="/public/statics/app.css">
    <script src="/public/statics/libs/jquery/dist/jquery.min.js"></script>
    <script src="/public/statics/app.js"></script>
</head>
<body<?php if(isset($controller)) echo ' data-controller="' . $controller . '"';?> ontouchstart>