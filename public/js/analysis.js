console.log(d3); //check if work


/*
d3.select('div')
    .selectAll('p')
    .data([1,2,3])
    .enter() //creates new selection
    .append('p')
    .text(dta => dta);

*/
/*
const sample_data = [
  { id: 'd1', value: 10, region: 'terra' },
  { id: 'd2', value: 20, region: 'aqua' },
  { id: 'd3', value: 15, region: 'ignis' },
  { id: 'd4', value: 25, region: 'ventus' }
];

const svg = d3.select('#d3test')
  .append('svg')
  .attr('width', 500)
  .attr('height', 500);

svg.selectAll('rect')
  .data(sample_data)
  .enter()
  .append('rect')
  .attr('x', (d, i) => i * 50)
  .attr('y', d => 120 - d.value * 10)   // flip so bars grow upward
  .attr('width', 40)
  .attr('height', d => d.value * 10)
  .attr('fill', 'red');
*/
  //pie
/*
const svg1 = d3.select('#deliquent')
               .append('svg', )       ``
               .attr('width')
               */
/*
 const pie_data = [1, 1, 2, 3, 5, 8, 13, 21];
const pie = d3.pie();
const arcs = pie(data);
[
  {"data":  1, "value":  1, "index": 6, "startAngle": 6.050474740247008, "endAngle": 6.166830023713296, "padAngle": 0},
  {"data":  1, "value":  1, "index": 7, "startAngle": 6.166830023713296, "endAngle": 6.283185307179584, "padAngle": 0},
  {"data":  2, "value":  2, "index": 5, "startAngle": 5.817764173314431, "endAngle": 6.050474740247008, "padAngle": 0},
  {"data":  3, "value":  3, "index": 4, "startAngle": 5.468698322915565, "endAngle": 5.817764173314431, "padAngle": 0},
  {"data":  5, "value":  5, "index": 3, "startAngle": 4.886921905584122, "endAngle": 5.468698322915565, "padAngle": 0},
  {"data":  8, "value":  8, "index": 2, "startAngle": 3.956079637853813, "endAngle": 4.886921905584122, "padAngle": 0},
  {"data": 13, "value": 13, "index": 1, "startAngle": 2.443460952792061, "endAngle": 3.956079637853813, "padAngle": 0},
  {"data": 21, "value": 21, "index": 0, "startAngle": 0.000000000000000, "endAngle": 2.443460952792061, "padAngle": 0}
];
svg.select("#deliquent")

*/

// Tree Graph Example
// Sample hierarchical data


//self learn area
/*


const data =[10,20,30];
const width = 200, height = 300;

const svg = d3.select('#d3test')
              .append('svg')
              .attr('width', width)
              .attr('height', height);

const pie = d3.pie();
const arc = d3.arc().outerRadius(100).innerRadius(0);

svg.selectAll('path')
              .data(pie(data))
              .enter()
              .attr('d',arch)
              .attr('transform',`translate(${width/2},${height/2})`)
              .attr('fill',(d,i) => d3.schemaCategory10[i %10]);
*/


//real code

//const container = document.getElementById("#d3test");

//const width = container.clientWidth;
//const height = container.clientHeight;

const sample_data = [
  { id: 'd1', value: 10 },
  { id: 'd2', value: 20 },
  { id: 'd3', value: 15 },
  { id: 'd4', value: 25 }
];

const container = document.getElementById("d3test");
const width = container.clientWidth;
const height = container.clientHeight;

const svg = d3.select('#d3test')
  .append('svg')
  .attr('width', width)
  .attr('height', height);

// SCALE (this is VERY important)
const xScale = d3.scaleBand()
  .domain(sample_data.map(d => d.id))
  .range([20, width - 20])
  .padding(0.2);

const yScale = d3.scaleLinear()
  .domain([0, d3.max(sample_data, d => d.value)])
  .range([height - 30, 20]);

// DRAW BARS
svg.selectAll('rect')
  .data(sample_data)
  .enter()
  .append('rect')
  .attr('x', d => xScale(d.id))
  .attr('y', d => yScale(d.value))
  .attr('width', xScale.bandwidth())
  .attr('height', d => height - 30 - yScale(d.value))
  .attr('fill', '#3b82f6');



  const pieData = [10, 20, 30, 40];

const container2 = document.getElementById("deliquent");
const width2 = container2.clientWidth;
const height2 = container2.clientHeight;
const radius = Math.min(width2, height2) / 2;

const svg2 = d3.select('#deliquent')
  .append('svg')
  .attr('width', width2)
  .attr('height', height2)
  .append('g')
  .attr('transform', `translate(${width2/2}, ${height2/2})`);

const pie = d3.pie();
const arc = d3.arc()
  .innerRadius(0)
  .outerRadius(radius - 10);

svg2.selectAll('path')
  .data(pie(pieData))
  .enter()
  .append('path')
  .attr('d', arc)
  .attr('fill', (d, i) => d3.schemeCategory10[i]);