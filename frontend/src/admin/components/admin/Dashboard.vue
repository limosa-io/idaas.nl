<template>

<div class="container-fluid">

  <div class="row stats">
    <div class="col-3" @click="$router.push('/oidc')">
      <span>{{ makeLargeReadable(dashboard.applications) }}</span>
      <span>Applications</span>
    </div>
    <div class="col-3" @click="$router.push('/users')">
      <span>{{ makeLargeReadable(dashboard.users) }}</span>
      <span>Users</span>
    </div>
    <div class="col-3" @click="$router.push('/users')">
      <span>{{ makeLargeReadable(dashboard.user_creations) }}</span>
      <span>New<br />Users</span>
    </div>
    <div class="col-3" @click="$router.push('/sessions/tokens')">
      <span>{{ makeLargeReadable(dashboard.tokens) }}</span>
      <span>Tokens</span>
    </div>

  </div>

  <div class="chart-container">
    <div class="mb-3">
      <h2>Authentications</h2>
      <span>last 30 days</span>
    </div>
    <canvas ref="chart1"></canvas>
  </div>

</div>

</template>

<style lang="scss">

.stats{
  
    padding: 21px;
    background-color: white;

    box-shadow: 0 10px 40px 0 rgba(62, 57, 107, 0.07), 0 2px 9px 0 rgba(62, 57, 107, 0.06);


     div {

       display: flex;
       align-items: center;
       padding-left: 30px;

       &:not(:last-child){
         border-right-style: solid;
        border-right-color: #B0BEC5;
        border-right-width: 1px;
       }
    
    height: 81px;

    > span:first-child{
        font-size: 30px;
        font-weight: 300;
        font-size: 4rem;
    }

    > span:last-of-type{
      margin-left: 20px;
      font-weight: 400;
      font-size: 1rem;
    }

  }
 
}

.chart-container {

  div {
    display: flex;
    align-items: end;
    

    h2 {
      text-transform: uppercase;
      float: left;
      font-size: 1.5rem;
      margin: 0px;
    }

    span {
      float: left;
      font-size: 1.5rem;
      margin-left: 10px;
      
      color: lighten(#72777a, 30%);
    }
  }

  box-shadow: 0 10px 40px 0 rgba(62, 57, 107, 0.07), 0 2px 9px 0 rgba(62, 57, 107, 0.06);

  margin-top: 30px;
  margin-left: -15px;
  margin-right: -15px;
  background-color: white;
  padding: 20px;
  max-height: 700px;
  
  position: relative; 
  padding-bottom: 60px;
  
}

</style>

<script>

import Chart from 'chart.js';

var config = {
  type: 'line',
  data: {
    labels: [
      1,
      2,
      3,
      4,
      5,
      6,
      7,
      8,
      9,
      10,
      11,
      12,
      13,
      14,
      15,
      16,
      17,
      18,
      19,
      20,
      21,
      22,
      23,
      24,
      25,
      26,
      27,
      28,
      29,
      30
    ],
    datasets: [{
      label: 'Authentications',
      backgroundColor: '#4285f4',
      borderColor: '#4285f4',

      data: [

      ],
      fill: true,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    title: {
      display: false,
      text: 'Chart.js Line Chart'
    },
    legend: {
      display: false
    },
    tooltips: {
      enabled: true
    },

    hover: {
      mode: 'nearest',
      intersect: true
    },
    scales: {
      xAxes: [{
        display: true,
        scaleLabel: {
          display: true,
          labelString: 'Day'
        }
      }],
      yAxes: [{
        display: true,
        scaleLabel: {
          display: true,
          labelString: 'Log ins'
        },
        ticks: {
          suggestedMin: 0,
          beginAtZero: true,
          userCallback: function(label, index, labels) {
              // when the floored value is the same as the value we have a whole number
              if (Math.floor(label) === label) {
                  return label;
              }

          },
        }
      }]
    }
  }
};

export default {


  data() {
    return {
      dashboard: {}
    }
  },

  methods: {


    makeLargeReadable(number){

      if(number > 1000000){
        return Math.round(number / 1000000.0) + 'M';
      }

      if(number > 1000){
        return Math.round(number / 1000.0) + 'K';
      }

      return number;

    }



  },

  mounted() {

    var ctx = this.$refs.chart1.getContext('2d');

    const chart = new Chart(ctx, config);

    this.$http.get(this.$murl('api/stats/dashboard')).then(response => {
      this.dashboard = response.data;
    });

    this.$http.get(this.$murl('api/stats/loginsPerDay30Days')).then(response => {

      chart.data.datasets[0].data = response.data;
      chart.update();

    }, response => {
      // error callback
    });

  }

}


</script>
