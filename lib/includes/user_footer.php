		</div>
		<hr class="invisible" />
		<div id="footer" class="span-24 last">
			Powered by <a href="http://github.com/darylhawes/fsip">FSIP</a> based on <a href="http://www.alkalineapp.com/">Alkaline</a> under MIT license.
			<?php 
			
			echo '<br />';
			echo Debugger::getErrors();
			echo Debugger::getDebugString();
			
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