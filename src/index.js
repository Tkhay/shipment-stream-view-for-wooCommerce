import './style.scss';
import { render, useState } from '@wordpress/element';
import { DragDropContext, Droppable, Draggable } from '@hello-pangea/dnd';
import apiFetch from '@wordpress/api-fetch';

const OrderTrackerBuilder = () => {
	const allWooStatuses = window.ssvfwwData?.allStatuses || [];
	const savedOrder = window.ssvfwwData?.savedOrder || [];

	// Initialize Active Steps
	const [steps, setSteps] = useState( savedOrder.length > 0 ? savedOrder : allWooStatuses );
	
	// Initialize Deleted/Inactive Steps
	const [deletedSteps, setDeletedSteps] = useState( () => {
		if ( savedOrder.length > 0 ) {
			const savedIds = savedOrder.map( s => s.id );
			return allWooStatuses.filter( status => ! savedIds.includes( status.id ) );
		}
		return [];
	} );
	
	const [editingId, setEditingId] = useState( null );
	const [isSaving, setIsSaving] = useState( false );
	const [saveMessage, setSaveMessage] = useState( '' );

	const saveSettings = () => {
		setIsSaving( true );
		setSaveMessage( 'Saving...' );
		
		apiFetch( {
			path: '/ssvfww/v1/save-settings',
			method: 'POST',
			data: { steps: steps },
		} )
		.then( () => {
			setSaveMessage( 'Settings saved successfully!' );
			setTimeout( () => {
				setSaveMessage( '' );
				setIsSaving( false );
			}, 2000 );
		} )
		.catch( ( error ) => {
			setSaveMessage( ' Error saving settings. Please try again.' );
			console.error( 'Save error:', error );
			setTimeout( () => {
				setSaveMessage( '' );
				setIsSaving( false );
			}, 3000 );
		} );
	};

	const resetToDefaults = () => {
		const defaultSteps = window.ssvfwwData?.defaultSteps || allWooStatuses;
		if ( window.confirm( 'RESET TO WOOCOMMERCE ORDER STATUS DEFAULTS (WITH EXCEPTION TYPES)?' ) ) {
			setIsSaving( true );
			setSaveMessage( 'Resetting...' );
			
			setSteps( defaultSteps );
			setDeletedSteps( [] );
			
			apiFetch( { 
				path: '/ssvfww/v1/save-settings', 
				method: 'POST', 
				data: { steps: defaultSteps } 
			} )
			.then( () => {
				setSaveMessage( 'Reset to defaults successfully!' );
				setTimeout( () => {
					setSaveMessage( '' );
					setIsSaving( false );
				}, 2000 );
			} )
			.catch( ( error ) => {
				setSaveMessage( 'Error resetting. Please try again.' );
				console.error( 'Reset error:', error );
				setTimeout( () => {
					setSaveMessage( '' );
					setIsSaving( false );
				}, 3000 );
			} );
		}
	};

	const handleOnDragEnd = ( result ) => {
		if ( ! result.destination ) return;
		const items = Array.from( steps );
		const [reorderedItem] = items.splice( result.source.index, 1 );
		items.splice( result.destination.index, 0, reorderedItem );
		setSteps( items );
	};

	const updateStepProperty = ( id, key, value ) => {
		setSteps( steps.map( step => step.id === id ? { ...step, [key]: value } : step ) );
	};

	// MOVE FROM ACTIVE TO DELETED
	const moveToDeleted = ( id ) => {
		const item = steps.find( s => s.id === id );
		setDeletedSteps( [...deletedSteps, item] );
		setSteps( steps.filter( s => s.id !== id ) );
	};

	// RESTORE FROM DELETED TO ACTIVE
	const restoreStep = ( id ) => {
		const item = deletedSteps.find( s => s.id === id );
		setSteps( [...steps, item] );
		setDeletedSteps( deletedSteps.filter( s => s.id !== id ) );
	};

	return (
		<div className='ssvfww-builder-container'>
			<div className='ssvfww-header'>
				<h1>Shipment Stream View for WooCommerce - Status Management</h1>
				<p>Rearrange the tracking steps by dragging them into the order you prefer.</p>
				<p>Choose <strong>Milestone</strong> for normal delivery stages (shown on the progress bar), or <strong>Exception</strong> for special cases (shown as alert cards).</p>
			</div>

			<div className='ssvfww-status-table'>
				<div className='ssvfww-table-header'>
					<div className='table-header-center'>Order</div>
					<div>Status Name &amp; Type</div>
					<div className='table-header-right'>Actions</div>
				</div>

				<DragDropContext onDragEnd={handleOnDragEnd}>
					<Droppable droppableId='steps'>
						{( provided ) => (
							<ul className='ssvfww-step-list' {...provided.droppableProps} ref={provided.innerRef}>
								{steps.map( ( step, index ) => (
									<Draggable key={step.id} draggableId={step.id} index={index}>
										{( provided ) => (
											<li className='ssvfww-list-item compact-row' ref={provided.innerRef} {...provided.draggableProps}>
												<div className='col-drag' {...provided.dragHandleProps}>
													<span className='dashicons dashicons-menu'></span>
												</div>
												<div className='col-content'>
													<div className='status-dot'></div>
													<div className='flex-text'>
														{editingId === step.id ? (
															<input 
																type='text' 
																value={step.label} 
																onChange={( e ) => updateStepProperty( step.id, 'label', e.target.value )} 
																onBlur={() => setEditingId( null )} 
																autoFocus 
															/>
														) : (
															<>
																<div className='label-text'>{step.label}</div>
																<div className='id-text'>ID: {step.id}</div>
																
																{/*TYPE Selector*/}
																<select 
																	className='ssvfww-type-select'
																	value={step.type || 'milestone'} 
																	onChange={( e ) => updateStepProperty( step.id, 'type', e.target.value )}
																>
																	<option value='milestone'>Milestone (Bar)</option>
																	<option value='exception'>Exception (Alert)</option>
																</select>
															</>
														)}
													</div>
												</div>
												<div className='col-actions'>
													<button onClick={() => setEditingId( step.id )} className='button-icon' title='Edit'>
														<span className='dashicons dashicons-edit'></span>
													</button>
													<button onClick={() => moveToDeleted( step.id )} className='button-icon delete' title='Delete'>
														<span className='dashicons dashicons-trash'></span>
													</button>
												</div>
											</li>
										)}
									</Draggable>
								) )}
								{provided.placeholder}
							</ul>
						)}
					</Droppable>
				</DragDropContext>
			</div>

			<div className='ssvfww-footer-sticky'>
				<div className='footer-btns'>
					<button onClick={resetToDefaults} className='btn-reset' disabled={isSaving}>RESET TO DEFAULTS</button>
					<button onClick={saveSettings} className='btn-save' disabled={isSaving}>
						{isSaving ? 'SAVING...' : 'SAVE CHANGES'}
					</button>
					{saveMessage && <span className='save-message'>{saveMessage}</span>}
				</div>
			</div>

			{/* RESTORED YOUR DELETED SECTION */}
			{deletedSteps.length > 0 && (
				<div className='ssvfww-deleted-section'>
					<h2 className='section-title'>Removed Statuses</h2>
					<div className='ssvfww-status-table'>
						{deletedSteps.map( step => (
							<div key={step.id} className='ssvfww-list-item compact-row deleted-row'>
								<div className='col-content'>
									<div className='status-dot status-dot-inactive'></div>
									<div className='flex-text'>
										<div className='label-text label-text-inactive'>{step.label}</div>
										<div className='id-text'>ID: {step.id}</div>
									</div>
								</div>
								<div className='col-actions'>
									<button onClick={() => restoreStep( step.id )} className='button button-secondary'>RESTORE</button>
								</div>
							</div>
						) )}
					</div>
				</div>
			)}
		</div>
	);
};

render( <OrderTrackerBuilder />, document.getElementById( 'ssvfww-admin-app' ) );