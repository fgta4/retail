import {fgta4grid} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4grid.mjs'
import {fgta4form} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4form.mjs'
import * as fgta4pages from '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4pages.mjs'
import * as fgta4pageslider from '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4pageslider.mjs'
import * as settings from './merchrv.settings.mjs'
import * as apis from './merchrv.apis.mjs'
import * as pList from './merchrv-list.mjs'
import * as pEdit from './merchrv-edit.mjs'
import * as pEditItemsgrid from './merchrv-itemsgrid.mjs'
import * as pEditItemsform from './merchrv-itemsform.mjs'
import * as pEditPreview from './merchrv-preview.mjs'
import * as pEditImportdata from './merchrv-importdata.mjs'
import * as pEditLog from './merchrv-log.mjs'



const pnl_list = $('#pnl_list')
const pnl_edit = $('#pnl_edit')
const pnl_edititemsgrid = $('#pnl_edititemsgrid')
const pnl_edititemsform = $('#pnl_edititemsform')
const pnl_editpreview = $('#pnl_editpreview')
const pnl_editimportdata = $('#pnl_editimportdata')
const pnl_editlog = $('#pnl_editlog')



var pages = fgta4pages;
var slider = fgta4pageslider;


export const SIZE = {width:0, height:0}


export async function init(opt) {
	// $ui.grd_list = new fgta4grid()
	// $ui.grd_edit = new fgta4grid()

	global.fgta4grid = fgta4grid
	global.fgta4form = fgta4form



	$ui.apis = apis
	document.getElementsByTagName("body")[0].style.margin = '5px 5px 5px 5px'

	opt.variancedata = global.setup.variancedata;
	settings.setup(opt);

	pages
		.setSlider(slider)
		.initPages([
			{panel: pnl_list, handler: pList},
			{panel: pnl_edit, handler: pEdit},
			{panel: pnl_edititemsgrid, handler: pEditItemsgrid},
			{panel: pnl_edititemsform, handler: pEditItemsform},
			{panel: pnl_editpreview, handler: pEditPreview},
			{panel: pnl_editimportdata, handler: pEditImportdata},
			{panel: pnl_editlog, handler: pEditLog}			
		], opt)

	$ui.setPages(pages)


	document.addEventListener('OnButtonHome', (ev) => {
		if (ev.detail.cancel) {
			return
		}

		ev.detail.cancel = true;
		ExitModule();
	})
	
	document.addEventListener('OnSizeRecalculated', (ev) => {
		OnSizeRecalculated(ev.detail.width, ev.detail.height)
	})	



	await PreloadData()

}


export function OnSizeRecalculated(width, height) {
	SIZE.width = width
	SIZE.height = height
}

export async function ExitModule() {
	$ui.home();
}



async function PreloadData() {
	
}