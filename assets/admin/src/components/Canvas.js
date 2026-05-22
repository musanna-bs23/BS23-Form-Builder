import ContainerField from './ContainerField';
import FieldCard from './FieldCard';

function getDraggedType(event) {
  return event.dataTransfer.getData('text/plain') || event.dataTransfer.getData('text');
}

export default function Canvas({ fields, onDropRoot, onDropContainer, selectedFieldId, onSelectField }) {
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
        <div className="bs23-builder__empty-state">Drop fields here</div>
      ) : (
        <div className="bs23-builder__field-list">
          {fields.map((field) => (
            field.type === 'container' ? (
              <ContainerField
                field={field}
                key={field.id}
                onDropField={onDropContainer}
                onSelectField={onSelectField}
                selectedFieldId={selectedFieldId}
              />
            ) : (
              <FieldCard
                field={field}
                key={field.id}
                onSelect={onSelectField}
                selected={selectedFieldId === field.id}
              />
            )
          ))}
        </div>
      )}
    </section>
  );
}
