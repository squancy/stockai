# stockai
An open-source service for analysing the most important Hungarian stocks.

Stock AI is a basic aritifical intelligence program that, with the help of some well-known indicators and moving averages, computes a single suggestion in order to decide whether a certain stock worth buying or not.<br>
The calculations, functions and AI are mainly written is JavaScript, the data gathering and manipulation on the server-side are in PHP and the structure and styling is simple HTML and CSS.<br>
All the data for the stocks are collected from data.portfolio.hu.

<hr>

<h3>Currenly available indicators</h3>
<b>RSI (Relative Strength Index)</b>
<p>The formula is <i>100 - [100 / (1 + U / D)]</i> where <i>U</i> indicates increasing prices during an <i>n</i> period and <i>D</i> indicates decreasing prices during an <i>n</i> period. On Stock AI it is calculated with a 14 day time interval.<p>
  
<b>Momentum</b>
<p>The formula is <i>(closing price of today - closing price n days before) / (closing price n days before) * 100 + 100. On Stock AI it is calculated with a 14 day time interval.</i></p>

<b>Stochastic</b>
<p>The formula is <i>%K = 100 * ((Z - L<sub>n</sub>) / (H<sub>n</sub> - L<sub>n</sub>))</i> and <i>%D = SMA3(%K)</i> where <i>Z</i> is the last closing price, <i>L<sub>n</sub></i> is the lowest price during the <i>n</i> period, <i>H<sub>n</sub></i> is the highest price during the <i>n</i> period and <i>SMA3</i> is a simple moving average with a 3 day time interval. On Stock AI it is calculated with a 6 day time interval.</p>
<br>
<h3>Moving averages</h3>
<b>SMA (Simple Moving Average)</b>
<p>The formula is <i>(n<sub>1</sub> + n<sub>2</sub> + n<sub>3</sub> + ... + n<sub>i</sub>) / i</i>.</p>

<b>EMA (Exponential Moving Average)</b>
<p>The formula is <i>(last closing price * x%) + (last EMA * (100 - x%))</i> where <i>x% = 2 / (1 + n)</i>, <i>n</i> is the time interval and for the first time (when there is no <i>last EMA</i>) <i>last EMA</i> is a simple moving average. Due to the lack of data, the first SMA time interval is calculated as follows: <br>- if <i>x%</i> >= 0.4 then <i>n = 5</i><br>- if 0.4 > <i>x%</i> >= 0.2 then <i>n = 7</i><br>- otherwise n = 9</i><br>On Stock AI there are EMA3, EMA9 and EMA14 calculated with a 3, 9 and 14 day time interval, respectively.</p>

<h3>Signs, weights and summary</h3>
<b>Strong sell</b>
<p>Indicates a confident and strong sign to sell a certain stock. Weighted as 2x.</p>
<b>Sell</b>
<p>Indicates a probable sign to sell a certain stock. Weighted as 1x.</p>
<b>Sell sign</b>
<p>Indicates an uncertain and weak sign to sell a certain stock. Weighted as 0.75x.</p>
<b>Neutral</b>
<p>Indicates uncertainty and indifference. Does not have a weight.</p>
<b>Buy sign</b>
<p>Indicates an uncertain and weak sign to buy a certain stock. Weighted as 0.75x.</p>
<b>Buy</b>
<p>Indicates a probable sign to buy a certain stock. Weighted as 1x.</p>
<b>Strong buy</b>
<p>Indicates a confident and strong sign to buy a certain stock. Weighted as 2x.</p>
<br>
<b>How summary is calculated?</b>
<p>Both sell and buy signs are summed up so that the maximum value for a certain stock is 13. Obviously, sell sum and buy sum cannot be 13 at the same time. If one of them has the maximum value the other one has to be 0, therefore either <i>sell sum - buy sum</i> or <i>buy sum - sell sum</i> will always return in a positive value. Taking this assertion to be true, the summary is calculated as follows: <br><p>- if <i>buy sum - sell sum</i> is positive and <i>buy sum - sell sum</i> >= 11 trade sign is <b>Strong buy</b></p><p>- else if <i>buy sum - sell sum</i> is positive and <i>buy sum - sell sum</i> >= 9 trade sign is <b>Buy</b></p><p>- else if <i>buy sum - sell sum</i> is positive and <i>buy sum - sell sum</i> >= 7 trade sign is <b>Buy sign</b></p><p>- else if <i>sell sum - buy sum</i> is positive and <i>sell sum - buy sum</i> >= 11 trade sign is <b>Strong sell</b></p><p>- else if <i>sell sum - buy sum</i> is positive and <i>sell sum - buy sum</i> >= 9 trade sign is <b>Sell</b></p><p>- else if <i>sell sum - buy sum</i> is positive and <i>sell sum - buy sum</i> >= 7 trade sign is <b>Sell sign</b></p><p>- else trade sign is <b>Neutral</b></p></p>

<h3>Email service</h3>
<p>You can also give your email address and every time a stock changes +/-2% you will get a notification about it.</p>

<h3>Nice TODOs</h3>
  * Replace the old AJAX request in `analysis.js` with the newer ES6 fetch API + async/await functions just like in `email.js`
  * Adding more indicators (due to the lack of data it might be a challenging problem)
  * A broader analysis on the stocks containing market sentiment, P/E ratio etc. (may mix both technical and fundamental analysis)
  * anything you think would be great ...

<h3>Trying out</h3>

```
git clone https://github.com/squancy/stockai/
```
You will need a local server to test it like XAMPP or LAMP and PHP + MySQL. The database in SQL can be found in `sql/db.sql`.<br>
Then open <b>main.php</b> in your browser and it should work fine (depending on the reliability of the 3rd party data provider).
<br><br>
<p>If you still have any question feel free to check <a href="https://www.pearscom.com/stockai">Stock AI</a> or contact me via <a href="mailto:mark.frankli@pearscom.com">e-mail</a>.</p>
