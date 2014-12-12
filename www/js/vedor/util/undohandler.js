muze.namespace('vedor.util.undohandler');

vedor.util.undohandler = ( function() {

	function undohandler( restoreFunc ) {

		this.stored = new Array();
		this.currentid = -1;
		this.maxid = -1;
		this.maxregid = -1;
		this.currentState = {};
	
		// define methods only once
		if (typeof(_undohandler_prototype_called) == 'undefined') {
			_undohandler_prototype_called = true;

			undohandler.prototype.reset = reset;
			undohandler.prototype.undo = undo;
			undohandler.prototype.redo = redo;
			undohandler.prototype.store = store;
			undohandler.prototype.checkUndo = checkUndo;
			undohandler.prototype.checkRedo = checkRedo;
		}

		function reset() {
			this.stored = new Array();
			this.currentid = -1;
			this.maxid = -1;
			this.maxregid = -2;
			this.currentState = {};
		}

		function checkRedo() {
				var tempid = this.currentid;
				var result = false;
				while (tempid < this.maxid) {
					tempid++;
					if (this.stored[tempid] && this.stored[tempid]['value'] != this.currentState[this.stored[tempid]['id']]) {
						return true;
					}
				}
				return false;
		}

		function checkUndo() {
			var tempid = this.currentid;
			var result = false;
			while (tempid > 0) {
				tempid--;
				if (this.stored[tempid] && this.stored[tempid]['value'] != this.currentState[this.stored[tempid]['id']]) {
					return true;
				}
			}
			return false;
		}

		function undo() {
			if( this.currentid > 0 ) {
				this.currentid--;
				if( this.stored[this.currentid] ) {
					var storeId = this.stored[this.currentid]['id'];
					var storeValue = this.stored[this.currentid]['value'];
					if (this.currentState[storeId] == storeValue) { //no change in this stored value, so go another step back
						return this.undo();
					} else {
						// restore value...
						restoreFunc( storeId, storeValue);
						var prevValue = this.currentState[storeId];
						this.currentState[storeId] = storeValue;
						return { 'id' : storeId, 'was' : prevValue };
					}
				} else {
					return this.undo();
				}
			}
		}

		function redo() {
			if( this.currentid < this.maxid ) {
				this.currentid++;
				if( this.stored[this.currentid] ) {
					var storeId = this.stored[this.currentid]['id'];
					var storeValue = this.stored[this.currentid]['value'];
					if (this.currentState[storeId] == storeValue) { // no change in stored value, so go another step forward.
						return this.redo();
					} else {
						// restore value...
						restoreFunc( storeId, storeValue );
						var prevValue = this.currentState[storeId];
						this.currentState[storeId] = storeValue;
						return { 'id' : storeId, 'was' : prevValue };
					}
				} else {
					return this.redo();
				}
			}
		}

		function store(storeId, storeValue) {
			// top.window.status='';
			//if (!this.currentState[storeId] || (storeValue != this.currentState[storeId])) {
				if (!this.currentState[storeId]) {
					var result=false; // only return true if a real change has been saved, not the first save
				} else {
					var result=true;
				}
				this.currentid++;
				this.stored[this.currentid] = new Array();
				this.stored[this.currentid]['id'] = storeId;
				this.stored[this.currentid]['value'] = storeValue;
				this.maxid=this.currentid;
				this.currentState[storeId] = storeValue;
				return result;
			//} else {
			//	return false;
			//}
		}

	}

	
	var self = {
		init : function( restoreFunc ) {
			return new undohandler( restoreFunc );
		}
	}
	return self;

})();