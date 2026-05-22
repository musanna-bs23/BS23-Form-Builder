import FieldCard from './FieldCard';

function getDraggedType(event) {
  return event.dataTransfer.getData('text/plain') || event.dataTransfer.getData('text');
}

export default function ContainerField({ field, onDropField }) {
  return (
    <section className="bs23-container-field" aria-label={`${field.columns} column container`}>
      <div
        className="bs23-container-field__columns"
        style={{ '--bs23-container-columns': field.columns }}
      >
        {field.children.map((column, index) => (
          <div
            aria-label={`Container column ${index + 1}`}
            className="bs23-container-field__column"
            key={`${field.id}-column-${index}`}
            onDragOver={(event) => event.preventDefault()}
            onDrop={(event) => {
              event.preventDefault();
              event.stopPropagation();
              onDropField(field.id, index, getDraggedType(event));
            }}
          >
            {column.length === 0 ? (
              <span className="bs23-container-field__empty">Drop here</span>
            ) : (
              column.map((child) => (
                child.type === 'container' ? (
                  <ContainerField field={child} key={child.id} onDropField={onDropField} />
                ) : (
                  <FieldCard field={child} key={child.id} />
                )
              ))
            )}
          </div>
        ))}
      </div>
    </section>
  );
}
