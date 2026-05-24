import { useState } from '@wordpress/element';

import BlockInserter from './BlockInserter';
import ContainerField from './ContainerField';
import FieldCard from './FieldCard';

function getDraggedType(event) {
  return event.dataTransfer.getData('text/plain') || event.dataTransfer.getData('text');
}

export default function Canvas({
  fields,
  onDropRoot,
  onDropContainer,
  selectedFieldId,
  onSelectField,
  onDelete,
  onDuplicate,
  onMove,
}) {
  const [inserterOpen, setInserterOpen] = useState(false);

  const addField = (type) => {
    onDropRoot(type);
    setInserterOpen(false);
  };

  return (
    <section
      aria-label="Form canvas"
      className="bs23-builder__canvas"
      onDragOver={(event) => event.preventDefault()}
      onDrop={(event) => {
        event.preventDefault();
        onDropRoot(getDraggedType(event));
      }}
    >
      {fields.length === 0 ? (
        <div className="bs23-builder__empty-state">Build your form with the plus button</div>
      ) : (
        <div className="bs23-builder__field-list">
          {fields.map((field) => (
            field.type === 'container' ? (
              <ContainerField
                field={field}
                key={field.id}
                onDropField={onDropContainer}
                onDelete={onDelete}
                onDuplicate={onDuplicate}
                onMove={onMove}
                onSelectField={onSelectField}
                selectedFieldId={selectedFieldId}
              />
            ) : (
              <FieldCard
                field={field}
                key={field.id}
                onDelete={onDelete}
                onDuplicate={onDuplicate}
                onMove={onMove}
                onSelect={onSelectField}
                selected={selectedFieldId === field.id}
              />
            )
          ))}
        </div>
      )}
      <div className="bs23-builder__insert-wrap">
        <button
          aria-label="Add field"
          className="bs23-builder__insert-button"
          onClick={() => setInserterOpen((open) => !open)}
          type="button"
        >
          +
        </button>
        {inserterOpen && (
          <BlockInserter
            onAddField={addField}
            onClose={() => setInserterOpen(false)}
          />
        )}
      </div>
    </section>
  );
}
