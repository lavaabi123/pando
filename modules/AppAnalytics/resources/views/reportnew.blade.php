
        <!-- PERFORMANCE OVERVIEW -->

<div class="section">

<h1>LinkedIn <span class="green">Analytics</span></h1>

<div class="metrics">

<div>
<h3>Impressions</h3>
<h2>12,260</h2>
</div>

<div>
<h3>Engagement</h3>
<h2>8.6%</h2>
</div>

<div>
<h3>Clicks</h3>
<h2>596</h2>
</div>

<div>
<h3>Members</h3>
<h2>6,272</h2>
</div>

<div>
<h3>Page Views</h3>
<h2>800</h2>
</div>

</div>

</div>



<!-- LINKEDIN PERFORMANCE -->

<div class="section">

<h1>LinkedIn <span class="green">Performance</span></h1>

<div class="chart-grid">

<canvas id="chart1" height="120"></canvas>
<canvas id="chart2" height="120"></canvas>

<canvas id="chart3" height="120"></canvas>
<canvas id="chart4" height="120"></canvas>

</div>

</div>



<!-- INDUSTRIES -->

<div class="section">

<h1>Audience <span class="green">Insights</span></h1>

<canvas id="industryChart" height="200"></canvas>

</div>



<!-- CONTENT PERFORMANCE -->

<div class="section">

<h1>Content <span class="green">Performance</span></h1>

<div class="content-grid">

<div class="post">

<h3>Post A</h3>

<canvas id="postAChart"></canvas>

<p>Likes 3000</p>
<p>Comments 1200</p>
<p>Shares 1500</p>

<div class="rate">5%</div>
<p>Engagement Rate</p>

</div>


<div class="post">

<h3>Post B</h3>

<canvas id="postBChart"></canvas>

<p>Likes 950</p>
<p>Comments 250</p>
<p>Shares 100</p>

<div class="rate">4%</div>
<p>Engagement Rate</p>

</div>

</div>

</div>



<!-- CAMPAIGN -->

<div class="section">

<h1>Campaign <span class="green">Analysis</span></h1>

<canvas id="roiChart" height="150"></canvas>

</div>



<!-- SENTIMENT -->

<div class="section">

<h1>Sentiment <span class="green">Analysis</span></h1>

<p>Positive → 70%</p>
<p>Neutral → 20%</p>
<p>Negative → 10%</p>

</div>



<form id="pdfForm" method="POST" action="{{ route('app.analytics.reportnew.pdf') }}">
@csrf

<input type="hidden" name="chart1" id="chart1_img">
<input type="hidden" name="chart2" id="chart2_img">
<input type="hidden" name="chart3" id="chart3_img">
<input type="hidden" name="chart4" id="chart4_img">

<input type="hidden" name="industry_chart" id="industry_chart_img">
<input type="hidden" name="post_a_chart" id="post_a_chart_img">
<input type="hidden" name="post_b_chart" id="post_b_chart_img">
<input type="hidden" name="roi_chart" id="roi_chart_img">

</form>


<button onclick="exportPDF()">Export PDF</button>
    </div>
</div>

@push('styles')
<style>
   body{
background:#000;
color:#fff;
font-family:Arial;
padding:40px;
}

h1{
font-size:60px;
font-weight:300;
}

.green{
color:#b7ff00;
}

.section{
margin-bottom:120px;
}

.metrics{
display:flex;
gap:80px;
margin-top:40px;
}

.metrics div{
text-align:center;
}

.chart-grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:60px;
margin-top:60px;
}

canvas{
background:#000;
}

.content-grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:100px;
margin-top:60px;
}

.post{
text-align:center;
}

.rate{
font-size:80px;
}

button{
padding:12px 30px;
font-size:18px;
cursor:pointer;
margin-top:50px;
}
</style>
@endpush
@endsection
@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

const lineData=[2000,4000,6000,8000];

const commonLine={
type:'line',
data:{labels:['May','June','July','August'],
datasets:[{data:lineData,borderColor:'#b7ff00'}]},
options:{plugins:{legend:{display:false}}}
};

const chart1=new Chart(document.getElementById('chart1'),commonLine);
const chart2=new Chart(document.getElementById('chart2'),commonLine);
const chart3=new Chart(document.getElementById('chart3'),commonLine);
const chart4=new Chart(document.getElementById('chart4'),commonLine);


const industryChart=new Chart(document.getElementById('industryChart'),{

type:'doughnut',

data:{
labels:['Financial','Gambling','IT','Technology','Software','Hospitality'],
datasets:[{
data:[40,30,15,10,10,10],
backgroundColor:['#a8c93a','#c8ff00','#8fa347','#6f7a50','#5c6548','#7c845a']
}]
}

});


const postAChart=new Chart(document.getElementById('postAChart'),{

type:'bar',

data:{labels:['Like','Comment','Share'],
datasets:[{data:[3000,1200,1500],backgroundColor:'#b7ff00'}]},

options:{indexAxis:'y'}

});


const postBChart=new Chart(document.getElementById('postBChart'),{

type:'bar',

data:{labels:['Like','Comment','Share'],
datasets:[{data:[950,250,100],backgroundColor:'#b7ff00'}]},

options:{indexAxis:'y'}

});


const roiChart=new Chart(document.getElementById('roiChart'),{

type:'bar',

data:{labels:['Campaign X','Campaign Y'],
datasets:[{data:[4,5],backgroundColor:'#b7ff00'}]}

});


function exportPDF(){

document.getElementById('chart1_img').value=chart1.toBase64Image();
document.getElementById('chart2_img').value=chart2.toBase64Image();
document.getElementById('chart3_img').value=chart3.toBase64Image();
document.getElementById('chart4_img').value=chart4.toBase64Image();

document.getElementById('industry_chart_img').value=industryChart.toBase64Image();
document.getElementById('post_a_chart_img').value=postAChart.toBase64Image();
document.getElementById('post_b_chart_img').value=postBChart.toBase64Image();
document.getElementById('roi_chart_img').value=roiChart.toBase64Image();

document.getElementById('pdfForm').submit();

}

</script>
@endsection