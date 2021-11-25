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
		</div>
		<legend><i class="fas fa-archway"></i> {{Mes wireguards}}</legend>
		<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				echo '<br/>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			?>
		</div>
	</div>

	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default eqLogicAction btn-sm roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a>
				<a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
				<a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a class="eqLogicAction cursor" aria-controls="home" role="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br />
				<div class="row">
					<div class="col-sm-6">
						<form class="form-horizontal">
							<fieldset>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Nom de l'équipement Wireguard}}</label>
									<div class="col-sm-4">
										<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
										<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement wireguard}}" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Objet parent}}</label>
									<div class="col-sm-4">
										<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
											<option value="">{{Aucun}}</option>
											<?php
											$options = '';
											foreach ((jeeObject::buildTree(null, false)) as $object) {
												$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
											}
											echo $options;
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Catégorie}}</label>
									<div class="col-sm-8">
										<?php
										foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
											echo '<label class="checkbox-inline">';
											echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
											echo '</label>';
										}
										?>

									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label"></label>
									<div class="col-sm-8">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />{{Activer}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />{{Visible}}</label>
									</div>
								</div>
								<br />
								<div class="form-group">
									<label class="col-sm-4 control-label">{{[Interface][Address] Addresse}}</label>
									<div class="col-sm-8">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="Address" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{[Interface][PrivateKey] Clef privée (client)}}</label>
									<div class="col-sm-8">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="PrivateKey" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{[Interface][PostUp] Post up}}</label>
									<div class="col-sm-8">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="PostUp" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{[Interface][PostDown] Post down}}</label>
									<div class="col-sm-8">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="PostDown" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{[Peer][Endpoint] Serveur (ip:port)}}</label>
									<div class="col-sm-8">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="Endpoint" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{[[Peer]PublicKey] Clef public}}</label>
									<div class="col-sm-8">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="PublicKey" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{[Peer][AllowedIPs] Ip autorisée}}</label>
									<div class="col-sm-8">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="AllowedIPs" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{[Peer][PresharedKey] Clef paratagée (optionnel)}}</label>
									<div class="col-sm-8">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="PresharedKey" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{[Peer][PeristentKeepalive] Keepalive (optionnel)}}</label>
									<div class="col-sm-8">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="PeristentKeepalive" />
									</div>
								</div>
							</fieldset>
						</form>
					</div>
					<div class="col-sm-6">
						<form class="form-horizontal">
							<fieldset>

							</fieldset>
						</form>
					</div>
				</div>

			</div>
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<br />
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th>{{Nom}}</th>
							<th>{{Type}}</th>
							<th>{{Action}}</th>
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