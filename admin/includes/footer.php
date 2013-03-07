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
</body>
</html>