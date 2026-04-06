import { computed, defineComponent, h, type PropType } from "vue";

type TargetLocation = string | { path?: string; name?: string };

function resolveTo(to: TargetLocation) {
  if (typeof to === "string") return to;
  return to.path ?? to.name ?? "/";
}

function navigate(to: TargetLocation, replace = false) {
  if (typeof window === "undefined") return;

  const url = resolveTo(to);
  if (replace) {
    window.location.replace(url);
    return;
  }

  window.location.assign(url);
}

export function useRouter() {
  return {
    push: (to: TargetLocation) => navigate(to, false),
    replace: (to: TargetLocation) => navigate(to, true),
  };
}

export const RouterLink = defineComponent({
  name: "RouterLink",
  props: {
    to: {
      type: [String, Object] as PropType<TargetLocation>,
      required: true,
    },
  },
  setup(props, { attrs, slots }) {
    const href = computed(() => resolveTo(props.to));

    return () =>
      h(
        "a",
        {
          ...attrs,
          href: href.value,
          onClick: (event: MouseEvent) => {
            attrs.onClick?.(event);
            if (
              event.defaultPrevented ||
              event.button !== 0 ||
              event.metaKey ||
              event.altKey ||
              event.ctrlKey ||
              event.shiftKey
            ) {
              return;
            }

            event.preventDefault();
            navigate(props.to, false);
          },
        },
        slots.default?.(),
      );
  },
});
