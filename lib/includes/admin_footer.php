
		</div>
		<hr class="invisible" />
		<div id="debug" class="span-24 quiet last">
<?php 
			echo Debugger::getErrors();
			echo '<br />';
			echo Debugger::getDebugString();

			echo Orbit::promptTasks();
?>
		</div>
		<div id="footer" class="span-24 last">
			Powered by <a href="http://github.com/darylhawes/fsip">FSIP</a> based on <a href="http://www.alkalineapp.com/">Alkaline</a> under MIT license.
		</div>
	</div>
	</body>
</html>