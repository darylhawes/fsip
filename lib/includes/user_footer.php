		</div>
		<hr class="invisible" />
		<div id="footer" class="span-24 last">
			Powered by <a href="http://github.com/darylhawes/fsip">FSIP</a> based on <a href="http://www.alkalineapp.com/">Alkaline</a> under MIT license.
			<?php 
			
			if(!empty($fsip)){
				if($fsip->returnConf('maint_debug')){
					$debug = $fsip->debug();
					echo 'Execution time: ' . round($debug['execution_time'], 3) . ' seconds. Queries: ' . $debug['queries']  . '. ';
				}
				
				echo FSIP::returnErrors();
			}
			
			echo Orbit::promptTasks();
			
			?>
		</div>
	</div>
		{if:Published_Public_Image_Count}
		<div>
			<span class="footer_stats">There are {Published_Public_Image_Count} images available.</span>
		</div>
		{/if:Published_Public_Image_Count}
</body>
</html>