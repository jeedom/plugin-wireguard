<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('wireguard');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br />
				<span>{{Ajouter}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br>
				<span>{{Configuration}}</span>
			</div>
		</div>
		<legend><i class="fas fa-archway"></i> {{Mes Wireguards}}</legend>
		<?php		if (count($eqLogics) == 0) {
			echo '<br/><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun équipement Wireguard n\'est paramétré}}</div>';
		} else {
			echo '<div class="input-group" style="margin:5px;">';
			echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>';
			echo '<div class="input-group-btn">';
			echo '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
			echo '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
			echo '</div>';
			echo '</div>';
			echo '<div class="eqLogicThumbnailContainer">';
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			echo '</div>';
		} ?>
	</div>

	<div class="col-xs-12 eqLogic" style="display:none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default eqLogicAction btn-sm roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span></a>
				<a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
				<a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a class="eqLogicAction cursor" aria-controls="home" role="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<form class="form-horizontal">
					<fieldset>
						<div class="col-lg-6">
							<legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Nom du client Wireguard}}</label>
								<div class="col-sm-7">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;">
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement wireguard}}">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Objet parent}}</label>
								<div class="col-sm-7">
									<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
										<option value="">{{Aucun}}</option>
										<?php	$options = '';
										foreach ((jeeObject::buildTree(null, false)) as $object) {
											$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
										}
										echo $options; ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Catégorie}}</label>
								<div class="col-sm-7">
									<?php	foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
										echo '<label class="checkbox-inline">';
										echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '">' . $value['name'];
										echo '</label>';
									} ?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Options}}</label>
								<div class="col-sm-7">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />{{Activer}}</label>
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />{{Visible}}</label>
								</div>
							</div>

							<legend><i class="fas fa-server"></i> [Interface]</legend>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Adresse}}
									<sup><i class="fas fa-question-circle tooltips" title="Address = {{adresse de l'interface client Wireguard}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="Address">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Clé privée}}
									<sup><i class="fas fa-question-circle tooltips" title="PrivateKey = {{clé privée du client Wireguard}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="PrivateKey">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Post-Up
									<sup><i class="fas fa-question-circle tooltips" title="PostUp = {{commandes à exécuter au démarrage de l'interface client Wireguard (facultatif)}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<textarea class="eqLogicAttr form-control autogrow" data-l1key="configuration" data-l2key="PostUp"></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Post-Down
									<sup><i class="fas fa-question-circle tooltips" title="PostDown = {{commandes à exécuter à l'arrêt de l'interface client Wireguard (facultatif)}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<textarea class="eqLogicAttr form-control autogrow" data-l1key="configuration" data-l2key="PostDown"></textarea>
								</div>
							</div>
						</div>

						<div class="col-lg-6">
							<legend><i class="fas fa-link"></i> [Peer]</legend>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Clé publique}}
									<sup><i class="fas fa-question-circle tooltips" title="PublicKey = {{renseigner la clé publique du serveur Wireguard}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="PublicKey">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Point terminal}}
									<sup><i class="fas fa-question-circle tooltips" title="Endpoint = {{adresse IP publique du serveur : port d'écoute (ip:port)}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="Endpoint">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{IPs autorisées}}
									<sup><i class="fas fa-question-circle tooltips" title="AllowedIPs = {{liste des adresses IP autorisées}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="AllowedIPs">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Clé pré-partagée}}
									<sup><i class="fas fa-question-circle tooltips" title="PresharedKey = {{renseigner la clé pré-partagée (facultatif)}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="PresharedKey">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Maintenir la connexion}}
									<sup><i class="fas fa-question-circle tooltips" title="PersistentKeepalive = {{délai de vérification de la liaison en secondes (facultatif)}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="PeristentKeepalive">
								</div>
							</div>
						</div>
					</fieldset>
				</form>
			</div>

			<div role="tabpanel" class="tab-pane" id="commandtab">
				<br>
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th>{{Nom}}</th>
							<th>{{Options}}</th>
							<th>{{Actions}}</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>

		</div>
	</div>
</div>

<?php
include_file('desktop', 'wireguard', 'js', 'wireguard');
include_file('core', 'plugin.template', 'js');
?>
