<?php

/******************************************************************************
	WP Business Intelligence Lite
	Author: WP Business Intelligence
	Website: www.wpbusinessintelligence.com
	Contact: http://www.wpbusinessintelligence.com/contactus/

	This file is part of WP Business Intelligence Lite.

    WP Business Intelligence Lite is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    WP Business Intelligence Lite is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with WP Business Intelligence Lite; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	
	You can find a copy of the GPL licence here:
	http://www.gnu.org/licenses/gpl-3.0.html
******************************************************************************/

class nvd3_lineChart
{
    var $xAxisFormat = ',.1f';
    var $yAxisFormat = ',.1f';
    var $xAxisLabel = 'x label';
    var $yAxisLabel = 'y label';
    var $x_axis_istime = false;
    var $dataSeries;
    var $showMaxMin=true;
    var $placeholder = NULL;
    var $showTooltips = "true";
    var $transitionDuration = "500";
    var $nvd3Settings = NULL;
    var $required_js_libs = NULL;
    var $hasTextLabels = false;

    public function __construct($chart)
    {
        $this->nvd3Settings = new nvd3_settings();
        $this->placeholder = new nvd3_placeholder('ph_'.str_replace(' ', '_', $chart->name));
        $this->x_axis_istime = $chart->x_axis_istime;
        $this->xAxisFormat = '.'.$chart->x_axis_precision.'f';
        $this->yAxisFormat = '.'.$chart->y_axis_precision.'f';
        $this->required_js_libs = array();

        if(is_array($chart->x_axis_labels->labels))
        {
            $this->hasTextLabels = true;
        }

        wp_enqueue_script('nvd3-fisheye', $this->nvd3Settings->wpbi_url['nvd3']['fisheye'], ['nvd3-nvd3'] );
        wp_enqueue_script('nvd3-tooltip', $this->nvd3Settings->wpbi_url['nvd3']['tooltip'], ['nvd3-nvd3'] );
        wp_enqueue_script('nvd3-utils', $this->nvd3Settings->wpbi_url['nvd3']['utils'], ['nvd3-nvd3'] );
        wp_enqueue_script('nvd3-legend', $this->nvd3Settings->wpbi_url['nvd3']['legend'], ['nvd3-nvd3'] );
        wp_enqueue_script('nvd3-scatter', $this->nvd3Settings->wpbi_url['nvd3']['scatter'], ['nvd3-nvd3'] );
        wp_enqueue_script('nvd3-axis', $this->nvd3Settings->wpbi_url['nvd3']['axis'], ['nvd3-nvd3'] );
        wp_enqueue_script('nvd3-line', $this->nvd3Settings->wpbi_url['nvd3']['line'], ['nvd3-nvd3'] );
        wp_enqueue_script('nvd3-linechart', $this->nvd3Settings->wpbi_url['nvd3']['linechart'], ['nvd3-nvd3'] );

    }
    public function create_dataseries($chart)
    {
        $this->dataSeries = array();

        // For now we support a single series, but this shall become a foreach loop
        // on the number of series (queries?)

        $count = 0;
        foreach ($chart->elements as $key => $value){
            $this->dataSeries[$key] = new nvd3_dataseries($chart, $value, $count);
            $count++;
        }

    }

    public function getCode()
    {
        $x_axis_text="";

        if(!$this->hasTextLabels)
        {
            $x_axis_text="
                    chart.xAxis
                        .showMaxMin(".$this->showMaxMin.")
                        .tickFormat(d3.format('". $this->xAxisFormat ."'));
                    ";
        }

        if($this->x_axis_istime)
        {
           return "nv.addGraph(function() {
                      var chart = nv.models.lineChart();

                      chart.xAxis
                          .tickFormat(function(d) {
                            return d3.time.format('%d/%m/%Y')(new Date(d))
                          });

                      chart.yAxis
                          .tickFormat(d3.format('".$this->yAxisFormat."'));

                      d3.select('#".$this->placeholder->name." svg')
                          .datum(nvd3Data_".$this->placeholder->name.")
                          .transition().duration(".$this->transitionDuration.")
                          .call(chart);

                      nv.utils.windowResize(chart.update);

                      return chart;
                    });
                    ";
        }
        else
        {
            return "nv.addGraph(function() {
                chart = nv.models.lineChart();

                chart.x(function(d, i) { return i });

                 ".$x_axis_text."

                chart.yAxis
                      .axisLabel('".$this->yAxisLabel."')
                      .tickFormat(d3.format('".$this->yAxisFormat."'));

                d3.select('#".$this->placeholder->name." svg')
                    .datum(nvd3Data_".$this->placeholder->name.")
                    .transition().duration(".$this->transitionDuration.")
                    .call(chart);

                 nv.utils.windowResize(chart.update);

                chart.dispatch.on('stateChange', function(e) { nv.log('New State:', JSON.stringify(e)); });

                return chart;

            });";
        }

    }

    // create the CSS style for the placeholder
    public function setPlaceholderStyle($chart)
    {
        $this->width = $chart->width;
        $this->height = $chart->height;
        $phStyle = array('width : '.$chart->width, 'height : '.$chart->height);

        $this->placeholder->addStyleElement('#'.$this->placeholder->name.' svg', $phStyle);
    }

    // get the HTML for the chart placeholder
    public function getPlaceholder()
    {
        return $this->placeholder->render();
    }

    public function getJSlibs()
    {
        return '';
    }

}
