var this_page_id;
var this_page_options;

import {fgta4slideselect} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4slideselect.mjs'
import * as hnd from  './merchorderin-edit-hnd.mjs'

const txt_caption = $('#pnl_edit-caption')


const btn_edit = $('#pnl_edit-btn_edit')
const btn_save = $('#pnl_edit-btn_save')
const btn_delete = $('#pnl_edit-btn_delete')
const btn_print = $('#pnl_edit-btn_print');

const btn_commit = $('#pnl_edit-btn_commit')
const btn_uncommit = $('#pnl_edit-btn_uncommit')
			

const btn_post = $('#pnl_edit-btn_post')
const btn_unpost = $('#pnl_edit-btn_unpost')



const pnl_form = $('#pnl_edit-form')
const obj = {
	txt_merchorderin_id: $('#pnl_edit-txt_merchorderin_id'),
	txt_merchorderin_descr: $('#pnl_edit-txt_merchorderin_descr'),
	txt_merchorderin_ref: $('#pnl_edit-txt_merchorderin_ref'),
	dt_merchorderin_date: $('#pnl_edit-dt_merchorderin_date'),
	dt_merchorderin_datedue: $('#pnl_edit-dt_merchorderin_datedue'),
	cbo_merchsea_id: $('#pnl_edit-cbo_merchsea_id'),
	cbo_unit_id: $('#pnl_edit-cbo_unit_id'),
	cbo_dept_id: $('#pnl_edit-cbo_dept_id'),
	cbo_partner_id: $('#pnl_edit-cbo_partner_id'),
	cbo_project_id: $('#pnl_edit-cbo_project_id'),
	txt_merchorderin_version: $('#pnl_edit-txt_merchorderin_version'),
	chk_merchorderin_iscommit: $('#pnl_edit-chk_merchorderin_iscommit'),
	txt_merchorderin_commitby: $('#pnl_edit-txt_merchorderin_commitby'),
	txt_merchorderin_commitdate: $('#pnl_edit-txt_merchorderin_commitdate'),
	chk_merchorderin_ispost: $('#pnl_edit-chk_merchorderin_ispost'),
	txt_merchorderin_postby: $('#pnl_edit-txt_merchorderin_postby'),
	txt_merchorderin_postdate: $('#pnl_edit-txt_merchorderin_postdate')
}


const rec_commitby = $('#pnl_edit_record-commitby');
const rec_commitdate = $('#pnl_edit_record-commitdate');		
		


let form;
let rowdata;

export async function init(opt) {
	this_page_id = opt.id;
	this_page_options = opt;

	txt_caption.template = txt_caption.html();
	var disableedit = false;


	form = new global.fgta4form(pnl_form, {
		primary: obj.txt_merchorderin_id,
		autoid: true,
		logview: 'trn_merchorderin',
		btn_edit: disableedit==true? $('<a>edit</a>') : btn_edit,
		btn_save: disableedit==true? $('<a>save</a>') : btn_save,
		btn_delete: disableedit==true? $('<a>delete</a>') : btn_delete,		
		objects : obj,
		OnDataSaving: async (data, options) => { await form_datasaving(data, options) },
		OnDataSaveError: async (data, options) => { await form_datasaveerror(data, options) },
		OnDataSaved: async (result, options) => {  await form_datasaved(result, options) },
		OnDataDeleting: async (data, options) => { await form_deleting(data, options) },
		OnDataDeleted: async (result, options) => { await form_deleted(result, options) },
		OnIdSetup : (options) => { form_idsetup(options) },
		OnViewModeChanged : (viewonly) => { form_viewmodechanged(viewonly) },
		OnRecordStatusCreated: () => {
			
		$('#pnl_edit_record_custom').detach().appendTo("#pnl_edit_record");
		$('#pnl_edit_record_custom').show();		
					
		}		
	});
	form.getHeaderData = () => {
		return getHeaderData();
	}


	btn_print.linkbutton({ onClick: () => { btn_print_click(); } });	
	


	btn_commit.linkbutton({ onClick: async () => { 
		var args = { action: 'commit', cancel: false, options: {}};
		if (typeof hnd.action_starting === 'function') {
			await hnd.action_starting(args);
		}
		if (args.cancel) {
			if (typeof args.cancelShowMessage === 'function') { args.cancelShowMessage(); }
			return;
		}
		btn_action_click({ action: args.action, options: args.options }); 
	} });

	btn_uncommit.linkbutton({ onClick: async () => { 
		var args = { action: 'uncommit', cancel: false, options: {}};
		if (typeof hnd.action_starting === 'function') {
			await hnd.action_starting(args);
		}
		if (args.cancel) {
			if (typeof args.cancelShowMessage === 'function') { args.cancelShowMessage(); }
			return;
		}
		btn_action_click({ action: args.action, options: args.options }); 
	} });

			
	// Generator: Approval Handler not exist
	btn_post.linkbutton({ onClick: () => { 
		if (typeof hnd.btn_post_click==='function') {
			hnd.btn_post_click(); 
		} else {
			btn_action_click({ action: 'post' }); 
		}
	} });
	btn_unpost.linkbutton({ onClick: () => { 
		if (typeof hnd.btn_unpost_click==='function') {
			hnd.btn_unpost_click(); 
		} else {
			btn_action_click({ action: 'unpost' }); 
		}
	} });

	// Generator: Object Handler not exist

	// Generator: Upload Handler not exist


	obj.cbo_merchsea_id.name = 'pnl_edit-cbo_merchsea_id'		
	new fgta4slideselect(obj.cbo_merchsea_id, {
		title: 'Pilih merchsea_id',
		returnpage: this_page_id,
		api: $ui.apis.load_merchsea_id,
		fieldValue: 'merchsea_id',
		fieldDisplay: 'merchsea_name',
		fields: [
			{mapping: 'merchsea_id', text: 'merchsea_id'},
			{mapping: 'merchsea_name', text: 'merchsea_name'}
		],

	})				
				
	obj.cbo_unit_id.name = 'pnl_edit-cbo_unit_id'		
	new fgta4slideselect(obj.cbo_unit_id, {
		title: 'Pilih Sub Account Unit',
		returnpage: this_page_id,
		api: $ui.apis.load_unit_id,
		fieldValue: 'unit_id',
		fieldDisplay: 'unit_name',
		fields: [
			{mapping: 'unit_id', text: 'ID', style: 'width: 100px'},
			{mapping: 'unit_name', text: 'Unit', style: 'width: auto'}
		],
		OnDataLoading: (criteria, options) => {
			
			if (typeof hnd.cbo_unit_id_dataloading === 'function') {
				hnd.cbo_unit_id_dataloading(criteria, options);
			}						
		},					

	})				
				
	obj.cbo_dept_id.name = 'pnl_edit-cbo_dept_id'		
	new fgta4slideselect(obj.cbo_dept_id, {
		title: 'Pilih Default Sub Account Departemen',
		returnpage: this_page_id,
		api: $ui.apis.load_dept_id,
		fieldValue: 'dept_id',
		fieldDisplay: 'dept_name',
		fields: [
			{mapping: 'dept_name', text: 'Dept', style: 'width: auto'},
			{mapping: 'depttype_name', text: 'Type', style: 'width: 200px'}
		],
		OnDataLoading: (criteria, options) => {
			
			if (typeof hnd.cbo_dept_id_dataloading === 'function') {
				hnd.cbo_dept_id_dataloading(criteria, options);
			}						
		},					
		OnSelected: (value, display, record, args) => {
			if (value!=args.PreviousValue ) {
				if (typeof hnd.cbo_dept_id_selected === 'function') {
					hnd.cbo_dept_id_selected(value, display, record, args);
				}
			}
		},

	})				
				
	obj.cbo_partner_id.name = 'pnl_edit-cbo_partner_id'		
	new fgta4slideselect(obj.cbo_partner_id, {
		title: 'Pilih Partner',
		returnpage: this_page_id,
		api: $ui.apis.load_partner_id,
		fieldValue: 'partner_id',
		fieldDisplay: 'partner_name',
		fields: [
			{mapping: 'partner_name', text: 'Partner', style: 'width: auto'},
			{mapping: 'partnertype_name', text: 'Type', style: 'width: 200px'}
		],
		OnDataLoading: (criteria, options) => {
			
			if (typeof hnd.cbo_partner_id_dataloading === 'function') {
				hnd.cbo_partner_id_dataloading(criteria, options);
			}						
		},					

	})				
				
	obj.cbo_project_id.name = 'pnl_edit-cbo_project_id'		
	new fgta4slideselect(obj.cbo_project_id, {
		title: 'Pilih project_id',
		returnpage: this_page_id,
		api: $ui.apis.load_project_id,
		fieldValue: 'project_id',
		fieldDisplay: 'project_name',
		fields: [
			{mapping: 'project_id', text: 'project_id'},
			{mapping: 'project_name', text: 'project_name'}
		],

	})				
				




	document.addEventListener('keydown', (ev)=>{
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			if (ev.code=='KeyS' && ev.ctrlKey==true) {
				if (!form.isInViewMode()) {
					form.btn_save_click();
				}
				ev.stopPropagation()
				ev.preventDefault()
			}
		}
	}, true)
	
	document.addEventListener('OnSizeRecalculated', (ev) => {
		OnSizeRecalculated(ev.detail.width, ev.detail.height)
	})	

	document.addEventListener('OnButtonBack', (ev) => {
		var element = document.activeElement;
		element.blur();
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			ev.detail.cancel = true;
			if (form.isDataChanged()) {
				form.canceledit(()=>{
					$ui.getPages().show('pnl_list', ()=>{
						form.setViewMode()
						$ui.getPages().ITEMS['pnl_list'].handler.scrolllast()
					})
				})
			} else {
				$ui.getPages().show('pnl_list', ()=>{
					form.setViewMode()
					$ui.getPages().ITEMS['pnl_list'].handler.scrolllast()
				})
			}
		
		}
	})

	document.addEventListener('OnButtonHome', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			if (form.isDataChanged()) {
				ev.detail.cancel = true;
				$ui.ShowMessage('Anda masih dalam mode edit dengan pending data, silakan matikan mode edit untuk kembali ke halaman utama.')
			}
		}
	})

	//button state
	if (typeof hnd.init==='function') {
		hnd.init({
			form: form,
			obj: obj,
			opt: opt,
			btn_action_click: (actionargs) => {
				if (typeof btn_action_click == 'function') {
					btn_action_click(actionargs);
				}
			}
		})
	}

}

export function OnSizeRecalculated(width, height) {
}

export function getForm() {
	return form
}

export function getCurrentRowdata() {
	return rowdata;
}

export function open(data, rowid, viewmode=true, fn_callback) {

	var caption = txt_caption.template;
	caption = caption.replace('{{STATE_BEG}}', '');
	caption = caption.replace('{{STATE_END}}', ' View');
	txt_caption.html(caption);


	rowdata = {
		data: data,
		rowid: rowid
	}

	var pOpt = form.getDefaultPrompt(false)
	var fn_dataopening = async (options) => {
		options.criteria[form.primary.mapping] = data[form.primary.mapping]
	}

	var fn_dataopened = async (result, options) => {
		var record = result.record;
		updatefilebox(record);

		/*
		if (result.record.unit_id==null) { result.record.unit_id='--NULL--'; result.record.unit_name='NONE'; }
		if (result.record.dept_id==null) { result.record.dept_id='--NULL--'; result.record.dept_name='NONE'; }
		if (result.record.partner_id==null) { result.record.partner_id='--NULL--'; result.record.partner_name='NONE'; }
		if (result.record.project_id==null) { result.record.project_id='--NULL--'; result.record.project_name='NONE'; }

		*/
		for (var objid in obj) {
			let o = obj[objid]
			if (o.isCombo() && !o.isRequired()) {
				var value =  result.record[o.getFieldValueName()];
				if (value==null ) {
					record[o.getFieldValueName()] = pOpt.value;
					record[o.getFieldDisplayName()] = pOpt.text;
				}
			}
		}
  		updaterecordstatus(record)

		/* handle data saat opening data */   
		if (typeof hnd.form_dataopening == 'function') {
			hnd.form_dataopening(result, options);
		}


		form.SuspendEvent(true);
		form
			.fill(record)
			.setValue(obj.cbo_merchsea_id, record.merchsea_id, record.merchsea_name)
			.setValue(obj.cbo_unit_id, record.unit_id, record.unit_name)
			.setValue(obj.cbo_dept_id, record.dept_id, record.dept_name)
			.setValue(obj.cbo_partner_id, record.partner_id, record.partner_name)
			.setValue(obj.cbo_project_id, record.project_id, record.project_name)
			.setViewMode(viewmode)
			.lock(false)
			.rowid = rowid


		/* tambahkan event atau behaviour saat form dibuka
		   apabila ada rutin mengubah form dan tidak mau dijalankan pada saat opening,
		   cek dengan form.isEventSuspended()
		*/   
		if (typeof hnd.form_dataopened == 'function') {
			hnd.form_dataopened(result, options);
		}


		/* commit form */
		form.commit()
		form.SuspendEvent(false); 
		updatebuttonstate(record)


		/* update rowdata */
		for (var nv in rowdata.data) {
			if (record[nv]!=undefined) {
				rowdata.data[nv] = record[nv];
			}
		}

		// tampilkan form untuk data editor
		if (typeof fn_callback==='function') {
			fn_callback(null, rowdata.data);
		}
		
	}

	var fn_dataopenerror = (err) => {
		$ui.ShowMessage('[ERROR]'+err.errormessage);
	}

	form.dataload(fn_dataopening, fn_dataopened, fn_dataopenerror)
	
}


export function createnew() {

	var caption = txt_caption.template;
	caption = caption.replace('{{STATE_BEG}}', 'Create New ');
	caption = caption.replace('{{STATE_END}}', '');
	txt_caption.html(caption);


	form.createnew(async (data, options)=>{
		// console.log(data)
		// console.log(options)
		form.rowid = null

		// set nilai-nilai default untuk form
		data.merchorderin_date = global.now()
		data.merchorderin_datedue = global.now()
		data.merchorderin_version = 0
		data.merchorderin_iscommit = '0'
		data.merchorderin_ispost = '0'

		data.merchsea_id = '0'
		data.merchsea_name = '-- PILIH --'
		data.unit_id = '--NULL--'
		data.unit_name = 'NONE'
		data.dept_id = '--NULL--'
		data.dept_name = 'NONE'
		data.partner_id = '--NULL--'
		data.partner_name = 'NONE'
		data.project_id = '--NULL--'
		data.project_name = 'NONE'

		if (typeof hnd.form_newdata == 'function') {
			// untuk mengambil nilai ui component,
			// di dalam handler form_newdata, gunakan perintah:
			// options.OnNewData = () => {
			// 		...
			// }		
			hnd.form_newdata(data, options);
		}

		rec_commitby.html('');
		rec_commitdate.html('');
		


		var button_commit_on = true;
		var button_uncommit_on = false;
		btn_commit.linkbutton(button_commit_on ? 'enable' : 'disable');
		btn_uncommit.linkbutton(button_uncommit_on ? 'enable' : 'disable');
		

		options.OnCanceled = () => {
			$ui.getPages().show('pnl_list')
		}

		$ui.getPages().ITEMS['pnl_edititemsgrid'].handler.createnew(data, options)


	})
}


export function getHeaderData() {
	var header_data = form.getData();
	if (typeof hnd.form_getHeaderData == 'function') {
		hnd.form_getHeaderData(header_data);
	}
	return header_data;
}

export function detil_open(pnlname) {
	if (form.isDataChanged()) {
		$ui.ShowMessage('Simpan dulu perubahan datanya.')
		return;
	}

	//$ui.getPages().show(pnlname)
	let header_data = getHeaderData();
	if (typeof hnd.form_detil_opening == 'function') {
		hnd.form_detil_opening(pnlname, (cancel)=>{
			if (cancel===true) {
				return;
			}
			$ui.getPages().show(pnlname, () => {
				$ui.getPages().ITEMS[pnlname].handler.OpenDetil(header_data)
			})
		});
	} else {
		$ui.getPages().show(pnlname, () => {
			$ui.getPages().ITEMS[pnlname].handler.OpenDetil(header_data)
		})
	}

	
}


function updatefilebox(record) {
	// apabila ada keperluan untuk menampilkan data dari object storage


	if (typeof hnd.form_updatefilebox == 'function') {
		hnd.form_updatefilebox(record);
	}
}

function updaterecordstatus(record) {
	// apabila ada keperluan untuk update status record di sini

	rec_commitby.html(record.merchorderin_commitby);
	rec_commitdate.html(record.merchorderin_commitdate);
		

	if (typeof hnd.form_updaterecordstatus == 'function') {
		hnd.form_updaterecordstatus(record);
	}
}

function updatebuttonstate(record) {
	// apabila ada keperluan untuk update state action button di sini

	/* action button */
	var button_commit_on = false;
	var button_uncommit_on = false;	
	
	if (record.merchorderin_iscommit=="1") {
		button_commit_on = false;
		button_uncommit_on = true;
		form.lock(true);		
	} else {
		button_commit_on = true;
		button_uncommit_on = false;
		form.lock(false);
	} 
	btn_commit.linkbutton(button_commit_on ? 'enable' : 'disable');
	btn_uncommit.linkbutton(button_uncommit_on ? 'enable' : 'disable');		
		

	if (typeof hnd.form_updatebuttonstate == 'function') {
		hnd.form_updatebuttonstate(record);
	}
}

function updategridstate(record) {
	var updategriddata = {}

	// apabila ada keperluan untuk update state grid list di sini

	var col_commit = 'merchorderin_iscommit';
	updategriddata[col_commit] = record.merchorderin_iscommit;	
	

	if (typeof hnd.form_updategridstate == 'function') {
		hnd.form_updategridstate(updategriddata, record);
	}

	$ui.getPages().ITEMS['pnl_list'].handler.updategrid(updategriddata, form.rowid);

}

function form_viewmodechanged(viewmode) {

	var caption = txt_caption.template;
	if (viewmode) {
		caption = caption.replace('{{STATE_BEG}}', '');
		caption = caption.replace('{{STATE_END}}', ' View');
	} else {
		caption = caption.replace('{{STATE_BEG}}', '');
		caption = caption.replace('{{STATE_END}}', ' Edit');
	}
	txt_caption.html(caption);


	var OnViewModeChangedEvent = new CustomEvent('OnViewModeChanged', {detail: {}})
	$ui.triggerevent(OnViewModeChangedEvent, {
		viewmode: viewmode
	})
}

function form_idsetup(options) {
	var objid = obj.txt_merchorderin_id
	switch (options.action) {
		case 'fill' :
			objid.textbox('disable') 
			break;

		case 'createnew' :
			// console.log('new')
			if (form.autoid) {
				objid.textbox('disable') 
				objid.textbox('setText', '[AUTO]') 
			} else {
				objid.textbox('enable') 
			}
			break;
			
		case 'save' :
			objid.textbox('disable') 
			break;	
	}
}


async function form_datasaving(data, options) {
	// cek dulu data yang akan disimpan,
	// apabila belum sesuai dengan yang diharuskan, batalkan penyimpanan
	//    options.cancel = true

	// Modifikasi object data, apabila ingin menambahkan variabel yang akan dikirim ke server
	// options.skipmappingresponse = ['unit_id', 'dept_id', 'partner_id', 'project_id', ];
	options.skipmappingresponse = [];
	for (var objid in obj) {
		var o = obj[objid]
		if (o.isCombo() && !o.isRequired()) {
			var id = o.getFieldValueName()
			options.skipmappingresponse.push(id)
			// console.log(id)
		}
	}

	if (typeof hnd.form_datasaving == 'function') {
		hnd.form_datasaving(data, options);
	}

}

async function form_datasaveerror(err, options) {
	// apabila mau olah error messagenya
	// $ui.ShowMessage(err.errormessage)
	console.error(err)
	if (typeof hnd.form_datasaveerror == 'function') {
		hnd.form_datasaveerror(err, options);
	}
	if (options.supress_error_dialog!=true) {
		$ui.ShowMessage('[ERROR]'+err.message);
	}

}


async function form_datasaved(result, options) {
	// Apabila tidak mau munculkan dialog
	// options.suppressdialog = true

	// Apabila ingin mengganti message Data Tersimpan
	// options.savedmessage = 'Data sudah disimpan cuy!'

	// if (form.isNewData()) {
	// 	console.log('masukan ke grid')
	// 	$ui.getPages().ITEMS['pnl_list'].handler.updategrid(form.getData())
	// } else {
	// 	console.log('update grid')
	// }


	var data = {}
	Object.assign(data, form.getData(), result.dataresponse)
	/*
	form.setValue(obj.cbo_unit_id, result.dataresponse.unit_name!=='--NULL--' ? result.dataresponse.unit_id : '--NULL--', result.dataresponse.unit_name!=='--NULL--'?result.dataresponse.unit_name:'NONE')
	form.setValue(obj.cbo_dept_id, result.dataresponse.dept_name!=='--NULL--' ? result.dataresponse.dept_id : '--NULL--', result.dataresponse.dept_name!=='--NULL--'?result.dataresponse.dept_name:'NONE')
	form.setValue(obj.cbo_partner_id, result.dataresponse.partner_name!=='--NULL--' ? result.dataresponse.partner_id : '--NULL--', result.dataresponse.partner_name!=='--NULL--'?result.dataresponse.partner_name:'NONE')
	form.setValue(obj.cbo_project_id, result.dataresponse.project_name!=='--NULL--' ? result.dataresponse.project_id : '--NULL--', result.dataresponse.project_name!=='--NULL--'?result.dataresponse.project_name:'NONE')

	*/

	var pOpt = form.getDefaultPrompt(false)
	for (var objid in obj) {
		var o = obj[objid]
		if (o.isCombo() && !o.isRequired()) {
			var value =  result.dataresponse[o.getFieldValueName()];
			var text = result.dataresponse[o.getFieldDisplayName()];
			if (value==null ) {
				value = pOpt.value;
				text = pOpt.text;
			}
			form.setValue(o, value, text);
		}
	}
	form.rowid = $ui.getPages().ITEMS['pnl_list'].handler.updategrid(data, form.rowid)
	var rowdata = {
		data: data,
		rowid: form.rowid
	}

	if (typeof hnd.form_datasaved == 'function') {
		hnd.form_datasaved(result, rowdata, options);
	}
}



async function form_deleting(data, options) {
	if (typeof hnd.form_deleting == 'function') {
		hnd.form_deleting(data, options);
	}
}

async function form_deleted(result, options) {
	$ui.getPages().show('pnl_list')
	$ui.getPages().ITEMS['pnl_list'].handler.removerow(form.rowid)

	if (typeof hnd.form_deleted == 'function') {
		hnd.form_deleted(result, options);
	}
}



function btn_print_click() {

	if (form.isDataChanged() || !form.isInViewMode()) {
		$ui.ShowMessage('Simpan dulu perubahan datanya.');
		return;
	}

	var id = form.getCurrentId();
	var variancename = this_page_options.variancename;
	var module = window.global.modulefullname;
	var renderto = 'formtemplate-standard.phtml';
	var format = 'format-01-a4-potrait';
	var reportmodule = `${module}/merchorderin.xprint?renderto=${renderto}&format=${format}`;

	// var header_data = getHeaderData();
	$ui.getPages().show('pnl_editpreview', ()=>{
		console.log('Preview Showed');
		$ui.getPages().ITEMS['pnl_editpreview'].handler.PreviewForm({
			id: id, 
			variancename: variancename,
			reportmodule: reportmodule
		});
	})

}	






async function btn_action_click(args) {
	if (form.isDataChanged() || !form.isInViewMode()) {
		$ui.ShowMessage('[WARNING]Simpan dulu perubahan data, dan tidak sedang dalam mode edit.');
		return;
	}


	var docname = 'Sales Order'
	var txt_version = obj.txt_merchorderin_version;
	var chk_iscommit = obj.chk_merchorderin_iscommit;
	
	
	var id = form.getCurrentId();

	Object.assign(args, {
		id: id,
		act_url: null,
		act_msg_quest: null,
		act_msg_result: null,
		act_do: null,
		use_otp: false,
		otp_message: `Berikut adalah code yang harus anda masukkan untuk melakukan ${args.action} ${docname} dengan no id ${id}`,
	});

	switch (args.action) {
		
		case 'commit' :
			args.xtion_version = '1.1';
			args.act_url = `${global.modulefullname}/xtion-commit`;
			args.act_msg_quest = `Apakah anda yakin akan <b>${args.action}</b> ${docname} no ${args.id} ?`;
			args.act_msg_result = `${docname} no ${args.id} telah di ${args.action}.`;
			args.act_do = (result) => {
				chk_iscommit.checkbox('check');
				
				form.commit();
			}
			break;

		case 'uncommit' :
			args.xtion_version = '1.1';
			args.act_url = `${global.modulefullname}/xtion-uncommit`;
			args.act_msg_quest = `Apakah anda yakin akan <b>${args.action}</b> ${docname} no ${args.id} ?`;
			args.act_msg_result = `${docname} no ${args.id} telah di ${args.action}.`;
			args.act_do = (result) => {
				chk_iscommit.checkbox('uncheck');
				
				form.setValue(txt_version, result.version);
				form.commit();
			}
			break;		
			
		
	
		default:
			if (typeof hnd.do_other_action == 'function') {
				hnd.do_other_action(args);
			}
	}

	
	if (args.cancel) { return } // batalkan xtion

	try {
		$ui.mask('wait..');
		var { doAction } = await import('../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4xtion.mjs');
		await doAction(args, (err, result) => {
			if (err) {
				$ui.ShowMessage('[WARNING]' + err.message);	
			} else {
				if (result.dataresponse!=undefined) { updaterecordstatus(result.dataresponse) };
				args.act_do(result);

				if (result.dataresponse!=undefined) {
					updatebuttonstate(result.dataresponse);
					updategridstate(result.dataresponse);
				}

				if (typeof hnd.action_done == 'function') {
					hnd.action_done(result, args);
				}

				if (args.act_msg_result!=='') $ui.ShowMessage('[INFO]' + args.act_msg_result);	
			}
		});
	} catch (err) {
		console.error(err);
		$ui.ShowMessage('[ERROR]' + err.message);
	} finally {
		$ui.unmask();
	}
}	
	
	