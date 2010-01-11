<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<ul>
<?php foreach ($this->languages AS $arrLang): ?>

<?php if ($arrLang['active']): ?>

<li class="active <?php echo $arrLang['class']; ?>"><span class="active">
<?php if ($this->useImages): ?><img src="<?php echo $arrLang['icon']; ?>" alt="<?php echo $arrLang['pageTitle']; ?>"<?php echo $arrLang['iconsize']; ?> />
<?php else: echo $arrLang['label']; endif; ?>
</span></li>

<?php else: ?>

<li<?php if(strlen($this->class)): ?> class="<?php echo $arrLang['class']; ?>"<?php endif; ?>>
<a href="<?php echo $arrLang['href']; ?>"<?php echo $arrLang['target']; ?> title="<?php echo $arrLang['pageTitle']; ?>"><?php if ($this->useImages): ?><img src="<?php echo $arrLang['icon']; ?>" alt="<?php echo $arrLang['pageTitle']; ?>"<?php echo $arrLang['iconsize']; ?> />
<?php else: echo $arrLang['label']; endif; ?></a>
</li>

<?php endif; ?>

<?php endforeach; ?>
</ul>
</div>
<!-- indexer::continue -->
