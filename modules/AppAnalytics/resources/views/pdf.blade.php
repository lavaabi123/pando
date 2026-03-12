<!DOCTYPE html>
<html>
<head>

<style>

body{
font-family: DejaVu Sans;
margin:0;
padding:0;
background:#000;
color:#fff;
}

.page{
width:100%;
height:100%;
padding:60px;
page-break-after:always;
}

.title{
font-size:48px;
font-weight:300;
margin-bottom:40px;
}

.highlight{
color:#b7ff00;
}

.section-title{
color:#b7ff00;
font-size:22px;
margin-bottom:20px;
}

.grid{
display:flex;
justify-content:space-between;
}

.col{
width:48%;
}

.chart{
width:100%;
margin-top:20px;
}

.metric{
margin-bottom:15px;
font-size:18px;
}

.big{
font-size:60px;
}

.center{
text-align:center;
}
</style>

</head>

<body style="background:#000;color:#fff;font-family:DejaVu Sans">

<h1>LinkedIn Analytics</h1>

<img src="{{ $chart1 }}" width="700">

<h1>Audience Insights</h1>

<img src="{{ $industry_chart }}" width="400">

<h1>Content Performance</h1>

<img src="{{ $post_a_chart }}" width="400">
<img src="{{ $post_b_chart }}" width="400">

<h1>Campaign Analysis</h1>

<img src="{{ $roi_chart }}" width="400">

<h1>Sentiment Analysis</h1>

Positive 70%  
Neutral 20%  
Negative 10%

</body>
</html>