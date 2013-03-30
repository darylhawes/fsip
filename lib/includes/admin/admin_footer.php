
		</div>
		<hr class="invisible" />
		<div id="footer" class="span-24 last">
			Powered by <a href="http://github.com/darylhawes/fsip">FSIP</a> based on <a href="http://www.alkalineapp.com/">Alkaline</a> under MIT license.
			<?php 
		
			$debugger = getDebugger();
			echo $debugger->returnErrors();

			if (returnConf('maint_debug')) {
				echo = $debugger->getDebugString().'<br />';
				print_r($_SESSION['fsip']['debug']);
			}
			
			echo Orbit::promptTasks();
			?>
		</div>
	</div>
	</body>
</html>